# Documentation Initiative PRD

**Product documented:** FREE Resellerclub & LogicBoxes Tools v2.19.1  
**Initiative type:** In-repo developer documentation (not a product feature PRD)  
**Date:** 2026-09-01  
**Status:** Delivered

## Problem statement

The RC & LB Tools addon ships with zero in-repo developer documentation. Operational knowledge lives on [resellerclub-mods.com](https://www.resellerclub-mods.com/whmcs/resellerclub-tools-docs.php). AI agents and integrators lack context on how the **license gate**, **admin home dashboard**, and **LogicBoxes API calls** interact—leading to misdiagnosis (e.g., funds widget visible while the addon page shows Invalid; cron bypass vs admin validation).

## Audiences

| Audience | Needs |
|----------|-------|
| **AI coding agents** | Constraints, file map, license-first rule, correlation matrix, no-secret policy |
| **WHMCS administrators** | Home dashboard health checks, license panel, API connectivity, currency mismatch behavior |
| **Integrators / support engineers** | Gate matrix, endpoint inventory, hook vs cron vs widget differences |

## Goals

1. Enable a new agent reading `AGENTS.md` to answer: *“Why does the funds widget show but the tool page shows Invalid?”*
2. Document every `rcfree_license` gate site in a single correlation matrix.
3. Provide Mermaid diagrams for license flow, home load, and widget API calls.
4. Document MIT open-source licensing and the relationship to bundled vendor EULA and runtime product licensing.

## Success criteria

- [x] `docs/` tree with substantive content (not placeholders)
- [x] Gate matrix covers 33+ entry points (bootstrap, hooks, cron, widget, tools)
- [x] Grep-backed evidence in `docs/evidence/verification-index.md`
- [x] Version 2.19.1 referenced consistently
- [x] No real secrets in documentation (placeholders only)
- [x] MIT license and open-source posture stated in `AGENTS.md` and compliance doc

## Out of scope

- PHP source modifications by downstream forks (permitted under MIT; not part of initial doc deliverable)
- Automated tests or CI pipelines
- `graphify-out/` generation (optional follow-up)
- Replacing canonical external documentation at resellerclub-mods.com

## Deliverables

| Artifact | Path |
|----------|------|
| Agent entry | `AGENTS.md` |
| Human index | `docs/README.md` |
| Architecture | `docs/architecture/overview.md`, `diagrams.md` |
| Deep dives | `docs/deep-dives/license-system.md`, `admin-home.md`, `api-calls.md` |
| Correlation | `docs/correlation/gate-matrix.md` |
| Compliance | `docs/compliance/eula-and-licensing.md` |
| Evidence | `docs/evidence/verification-index.md` |

## Risks and mitigations

| Risk | Mitigation |
|------|------------|
| Conflicting vendor EULA in bundled drop-in | Compliance doc clarifies MIT repo vs `EULA.txt` vs runtime keys |
| Five duplicate `resellerclubmods_tools_output()` copies | Documented as maintenance hazard; single gate matrix |
| Secret leakage in examples | Placeholder tokens only; evidence scan |
| External docs supersede in-repo | README links canonical URLs; version pinned |

## Acceptance

Human operator confirms the doc pack is usable for onboarding and incident triage. Audit ledger entry created under `~/.ai-audit/ledger/`.
