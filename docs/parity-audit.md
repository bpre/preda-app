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
- Website static pages, dynamic public records, and legacy redirects, including
  calendar redirects and blog aliases, are covered by a real-data public smoke
  test.
- Website CMS resources are present in the `cms` panel.
- Website CMS list pages plus create/edit forms for posts, sentences, banks,
  credits, courts/judges, securities, FAQs, cities, offices, reviews, page
  snapshots, Pipedrive mappings, and team profiles are covered by a real-data
  smoke test.
- Website leads and offer inquiries are intentionally registered in `crm`, not `cms`.
- Ewidencja lead and potential matter resources are intentionally registered in `crm`.
- CRM list pages plus create/edit/view pages for Ewidencja leads/potential matters
  and website leads/offers are covered by a real-data smoke test.
- Letter notifications jobs, mail classes, templates, print views, file preview routes,
  and branch report export code are present.
- Ewidencja list pages plus create/edit/view forms for active operational resources
  are covered by a real-data resource smoke test.
- Ewidencja letter file preview/download, offer PDF download, branch report export,
  and correspondence envelope/send-list PDF generation are covered by a real-data
  operations smoke test.
- Selected side-effect workflows are covered by a transaction-safe real-data smoke
  test: website lead status changes, letter notification mail rendering with an
  attachment, offer notification mail preparation with a PDF attachment, and
  `r8dsg` mailing links for offer/remove requests.
- Legacy `/kancelaria/...` Ewidencja panel paths redirect to the new root paths on
  the `ewidencja` subdomain.

## Adjusted for safety

- CMS `Pracownicy` is restored as public website team profile management.
- CMS `Pracownicy` does not allow user creation or deletion.
- CRM does not register employee/user administration.
- Public family-law pages (`/rozwod`, `/podzial-majatku`) remain controlled by the
  existing `family-law` practice context flag.
- Portal user administration exists, but portal product development is not a priority.
- Imported Google Business Profile tokens encrypted with another `APP_KEY` no
  longer crash the reviews list; the connection is treated as requiring
  reauthorization.

## Known non-code gaps

- Local storage audit after `k1` and `k2` sync:
  - `letters.files`: 12271/12272 files present; 1 missing file.
  - `stages.files`: 13/13 files present.
  - `offers.pdf_path`: 86/86 files present.
  - `neostamps.generated_file`: 0/2643 files present; old `neoznaczki`
    storage still missing, so direct neostamp file download cannot yet be
    verified on a real local file.
  - `website_leads.files`: 0/1477 files present; old `preda.info` storage still missing.
  - `website_sentences.files`: 0/374 files present; old public storage still missing.
  - `website_securities.files`: 0/35 files present; old public storage still missing.
- The current Codex session cannot fetch the remaining website files over SSH without
  credentials, so those must be synced separately.

## Next audit targets

- Sync remaining old `preda.info` storage files, then rerun `legacy:audit-files`.
- Compare public pages visually against the old `preda.info` pages on real data after
  public storage files are available.
- Exercise remaining Livewire-only action paths manually or with browser automation
  once needed, especially full offer sending and queued letter notification actions.
