# STATE — RC & LB Tools (resellerclubmods_tools)

**Updated:** 2026-11-23 IST · **Writer:** continuity pass (docs only)
**Version:** 2.19.5 · **Default branch SHA:** 9d520cc
**Prod:** LIVE-DRIFTED. The prod copy has been older than main; confirm the baked version before assuming parity. No CHANGELOG exists, so none is checked.

## In flight

- Local branch `fix/rc-preupgrade-20261123` (2026-11-23 pre-9.1 audit, not pushed yet): promo TLD exclusion port, Move Domain input typing (XSS), sidebar null guards, PHP 8 warnings, public-doc scrub. Details: `docs/handoff/STATE.md`, `docs/handoff/TASKS.md`. Tests pass on PHP 7.4/8.3/8.4.

## Next

- Bake of 2.19.5 into the prod image is WHMCS-Prod's step (see the bake notes in `AGENTS.md`); not done from this repo.

## Last session

- Continuity pass: appended gates and continuity section to `AGENTS.md`, pointers, ADR 0001, check script, `pre-push` hook. No product code changed.
