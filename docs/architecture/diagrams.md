# Architecture Diagrams

Mermaid diagrams for RC & LB Tools v2.19.1. Node IDs use camelCase; edge labels with special characters are quoted.

## License validation flow

```mermaid
flowchart TB
    subgraph entry [EntryPoints]
        AdminModule["addonmodules.php"]
        CronInclude["cron includes tools.php"]
    end

    subgraph readKey [ReadLicense]
        TblAddon["tbladdonmodules.rcfree_license"]
        ModTools["mod_resellerclubmodstools.localkey"]
    end

    subgraph check [rcfree_check_license]
        LocalDecode["Decode localkey MD5"]
        CacheValid{"Cache valid 7d?"}
        DomainCheck["Validate domain IP dir"]
        RemotePre["POST preverify.php"]
        RemoteVerify["POST verify.php"]
        Md5Check["MD5 checksum"]
        FailGrace{"Remote fail within 9d grace?"}
    end

    subgraph outcomes [StatusSwitch]
        Active["Active output router"]
        Invalid["Invalid output UI"]
        Expired["Expired output UI"]
        Suspended["Suspended output UI"]
        Default["default empty key UI"]
    end

    subgraph frlbt [FRLBTAddon]
        AddonParse["Parse addons pipe string"]
        FRLBTCheck{"FRLBT active for release?"}
    end

    AdminModule --> TblAddon
    CronInclude --> TblAddon
    TblAddon -->|non-empty| ModTools
    ModTools --> LocalDecode --> CacheValid
    CacheValid -->|yes| DomainCheck
    DomainCheck -->|pass| Active
    DomainCheck -->|fail| RemotePre
    CacheValid -->|no| RemotePre
    RemotePre --> RemoteVerify --> Md5Check
    Md5Check -->|Active| EncodeLocal["Encode new localkey"]
    EncodeLocal --> Active
    Md5Check -->|other| Invalid
    RemoteVerify -->|HTTP not 200| FailGrace
    FailGrace -->|yes| Active
    FailGrace -->|no| Invalid

    Active --> AddonParse --> FRLBTCheck
    FRLBTCheck -->|fail| Invalid
    FRLBTCheck -->|pass| Active

    CronInclude -->|argv contains cron or sync| ForceActive["Force Active skip remote"]
    ForceActive --> Active
```

**Cross-reference:** Cron argv bypass at `resellerclubmods_tools.php:210-211`. Widget path does **not** appear in this diagram—it skips `rcfree_check_license()` entirely (see Widget diagram).

## Admin home load sequence (post-OSS)

```mermaid
sequenceDiagram
    participant Admin as WHMCS Admin
    participant Router as resellerclubmods_tools_output
    participant API as LogicBoxes API
    participant Home as tools/home.php

    Admin->>Router: GET addonmodules.php module=resellerclubmods_tools
    Router->>API: GET /api/resellers/details.json
    API-->>Router: sellingcurrency parentsellingcurrency
    Router->>Home: include default branch
    alt API ERROR or empty
        Home-->>Admin: API failed panel with SERVER_ADDR diagnostic
    else currency mismatch
        Home-->>Admin: Set session rcm_wrong_currencysetup redirect loop
    else OK
        Home-->>Admin: Account setup matrix version panel tool links
    end
```

**Evidence:** API test in `resellerclubmods_tools_output()`; IP diagnostic from `$_SERVER` in `home.php`; currency loop in `home.php`.

## AdminHomeWidget API calls

```mermaid
flowchart LR
    subgraph gate [WidgetGate]
        ReadCfg["Read tbladdonmodules"]
        FundsOn{"fundsbalance enabled?"}
    end

    subgraph accounts [UpToFourAccounts]
        Acc1["first_acc_name"]
        Acc2["second_acc_name"]
        Acc3["third_acc_name"]
        Acc4["fourth_acc_name"]
    end

    subgraph perAcc [PerAccountCalls]
        Details["GET /api/resellers/details.json"]
        Balance["GET /api/billing/reseller-balance.json"]
    end

    subgraph render [WidgetOutput]
        HTML["Funds HTML plus config links"]
        VerStatic["Static version from config"]
    end

    ReadCfg --> FundsOn
    FundsOn -->|no| Absent["Widget not returned"]
    FundsOn -->|yes| Acc1
    FundsOn --> Acc2
    FundsOn --> Acc3
    FundsOn --> Acc4

    Acc1 -->|show_fundsbalance not on| Details --> Balance
    Acc2 -->|show_fundsbalance not on| Details
    Acc3 -->|show_fundsbalance not on| Details
    Acc4 -->|show_fundsbalance not on| Details

    Balance --> HTML --> VerStatic
```

**Important:** Widget is gated on `fundsbalance` config only; no license or remote version check.

## Hook event fan-out

```mermaid
flowchart TB
    WHMCS["WHMCS Event"]
    HookFile["includes/hooks/resellerclubmods_*.php"]
    FeatureGate{"Feature flag on?"}
    CallAPI["call_api LogicBoxes"]
    NoOp["Silent no-op"]

    WHMCS --> HookFile --> FeatureGate
    FeatureGate -->|no| NoOp
    FeatureGate -->|yes| CallAPI
```

## Diagram ↔ gate matrix cross-walk

| Diagram node | Gate matrix row |
|--------------|-----------------|
| `ForceActive` | Main bootstrap cron bypass; cron scripts |
| `KeyPresent` | AdminHomeWidgets |
| `Active output router` | Active case + inherited tools |
| `Invalid` / `Expired` / `Suspended` | Matching switch cases |
| Hook `LicGate` | 10 `includes/hooks` files |
| `domainpricelist` | Not in these diagrams—see gate matrix widget row |
