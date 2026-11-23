# STATE — whmcs-free-resellerclubmods (RC & LB Tools 2.19.5)

Rewritten 2026-11-23 IST. Rewrite, don't append. ≤60 lines.
This repository is public: no secrets, hostnames, container names, digests or account ids in these files.


- Branch: `fix/rc-preupgrade-20261123` (local, not pushed)
- Base: `9d520cc`; head: `git log --oneline 9d520cc..HEAD`

## True now
- Addon dir and both promo hooks equal the prod overlay (promo TLD exclusion ported).
- Move Domain reflected XSS fixed; sidebar chains null-checked; public docs no longer describe production.
- scripts/verify-security.sh exit 0.

## Next action
Vendor review, then PR. Decide whether to rewrite git history for the topology text removed in dd46d20.

## Tests
`bash scripts/verify-security.sh`
