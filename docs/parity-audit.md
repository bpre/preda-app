# Legacy Parity Audit

Goal: reach functional parity with the existing `ewidencja.preda.info` and
`preda.info` applications before adding new product functionality.

## Current rules

- Client portal work is parked unless it is needed for safety or parity.
- CRM contains acquisition workflows moved from the old applications.
- CMS contains public website management only.
- Ewidencja contains operational law-office work and employee administration.

## Confirmed parity areas

- Ewidencja operational Filament resources are present in the `kancelaria` panel.
- Website public routes from `preda.info` are present on the public domain.
- Website static pages, dynamic public records, and legacy redirects are covered by
  a real-data public smoke test.
- Website CMS resources are present in the `cms` panel.
- Website CMS create/edit forms for posts, sentences, banks, credits, courts/judges,
  securities, FAQs, cities, offices, reviews, and team profiles are covered by a
  real-data smoke test.
- Website leads and offer inquiries are intentionally registered in `crm`, not `cms`.
- Ewidencja lead and potential matter resources are intentionally registered in `crm`.
- Letter notifications jobs, mail classes, templates, print views, file preview routes,
  and branch report export code are present.
- Ewidencja letter file preview/download, offer PDF download, and branch report export
  are covered by a real-data operations smoke test.
- Legacy `/kancelaria/...` Ewidencja panel paths redirect to the new root paths on
  the `ewidencja` subdomain.

## Adjusted for safety

- CMS `Pracownicy` is restored as public website team profile management.
- CMS `Pracownicy` does not allow user creation or deletion.
- CRM does not register employee/user administration.
- Public family-law pages (`/rozwod`, `/podzial-majatku`) remain controlled by the
  existing `family-law` practice context flag.
- Portal user administration exists, but portal product development is not a priority.

## Known non-code gaps

- Local storage audit after `k1` and `k2` sync:
  - `letters.files`: 1 missing file.
  - `stages.files`: complete.
  - `offers.pdf_path`: complete.
  - `website_leads.files`: missing files from the old `preda.info` storage.
  - `website_sentences.files`: missing public files from the old `preda.info` storage.
  - `website_securities.files`: missing public files from the old `preda.info` storage.
- The current Codex session cannot fetch the remaining website files over SSH without
  credentials, so those must be synced separately.

## Next audit targets

- Compare public pages visually against the old `preda.info` pages on real data.
- Exercise key Ewidencja create/edit/print/download actions on real records.
- Verify CMS edit flows for posts, sentences, banks, offices, reviews, FAQs, and team.
- Verify CRM lead and offer workflows using imported production data.
