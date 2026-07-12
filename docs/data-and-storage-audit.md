# Data And Storage Audit

Date: 2026-07-11

Goal: verify that `preda_app_local_fresh` contains the imported data from the
legacy `ewidencja.preda.info` and `preda.info` databases, then confirm the local
storage state after file sync.

## Commands Used

```sh
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=preda_app_local_fresh DB_USERNAME=root DB_PASSWORD= php artisan legacy:import-data --dry-run
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=preda_app_local_fresh DB_USERNAME=root DB_PASSWORD= php artisan legacy:audit-files --limit=20
```

## Data Count Result

The functional data tables match the legacy sources after applying the intended
merge and cleanup rules.

### Kancelaria

All mapped `ewidencja.preda.info` tables match the target counts exactly except:

| Source table | Source rows | Target table | Target rows | Result |
| --- | ---: | --- | ---: | --- |
| `contact_letter` | 4420 | `contact_letter` | 4418 | Accepted cleanup |
| `permissions` | 326 | `permissions` | 644 | Expected access merge |
| `role_has_permissions` | 769 | `role_has_permissions` | 1106 | Expected access merge |
| `model_has_roles` | 14 | `model_has_roles` | 19 | Expected access merge |
| `model_has_permissions` | 0 | `model_has_permissions` | 15 | Expected panel access |

Accepted `contact_letter` cleanup:

| Source id | Missing legacy `letter_id` | `contact_id` |
| ---: | --- | --- |
| 3837 | `911c61b8-cb24-4088-8fb1-4377cb42bd4a` | `e2220b95-61c6-4381-8c3c-65032ceb5d0b` |
| 4164 | `7c9cd224-6c4f-4586-a0b3-2d52e971940d` | `ad946ec1-870e-4eb4-b599-dee548b93b57` |

These two rows point to letters that do not exist in the legacy `letters` table,
so the import cleanup removes them.

### Website

All mapped `preda.info` content tables match the target counts exactly:

| Source table | Source rows | Target table | Target rows |
| --- | ---: | --- | ---: |
| `banks` | 26 | `website_banks` | 26 |
| `cities` | 31 | `website_cities` | 31 |
| `contacts` | 129 | `website_contacts` | 129 |
| `credits` | 34 | `website_credits` | 34 |
| `faqs` | 7 | `website_faqs` | 7 |
| `google_business_profile_connections` | 1 | `website_google_business_profile_connections` | 1 |
| `leads` | 330 | `website_leads` | 330 |
| `lead_status_changes` | 330 | `website_lead_status_changes` | 330 |
| `offices` | 5 | `website_offices` | 5 |
| `posts` | 45 | `website_posts` | 45 |
| `reviews` | 115 | `website_reviews` | 115 |
| `securities` | 43 | `website_securities` | 43 |
| `sentence_content_templates` | 17 | `website_sentence_content_templates` | 17 |
| `sentences` | 322 | `website_sentences` | 322 |

`pipedrives` was originally imported as `website_pipedrives` with `136` rows, but
this one-off reactivation module was removed from the new application after the
delete-request list was extracted.

`page_snapshots` was originally imported as `website_page_snapshots` with `485`
rows, but this legacy SEO audit module was removed from the new application.

`offers` was originally imported as `website_offers` with `16` rows, but this
legacy offer-request module was removed from the new application. The public
analysis form is the only current acquisition form.

### Users And Access

User and access tables are intentionally not a plain source-to-target copy:

- Employee users come from `ewidencja.preda.info.users`: `12 -> 12`.
- `preda.info.users` has 8 records. 7 match target users by email and are merged
  into website profile fields.
- One legacy website user, `bartosz.preda@gmail.com`, has no matching employee
  user by email and is not imported as an employee user.
- Duplicate roles between the source apps are merged by name and guard:
  `panel_user`, `super_admin`.
- Duplicate permissions between the source apps are merged by name and guard:
  `create_role`, `delete_any_role`, `delete_role`, `update_role`,
  `view_any_role`, `view_role`.
- New target-only permissions are expected:
  `access_kancelaria_panel`, `access_crm_panel`, `access_cms_panel`,
  `view_any_portal::user`, `view_portal::user`, `create_portal::user`,
  `update_portal::user`.

## Storage Audit Result

| Source | Disk | Records | Referenced files | Existing files | Missing files | Status |
| --- | --- | ---: | ---: | ---: | ---: | --- |
| `letters.files` | local | 7855 | 12272 | 12271 | 1 | Accepted legacy missing file |
| `stages.files` | local | 12 | 13 | 13 | 0 | OK |
| `offers.pdf_path` | local | 86 | 86 | 86 | 0 | OK |
| `neostamps.generated_file` | local | 2643 | 2643 | 2643 | 0 | OK |
| `website_leads.files` | local | 311 | 1477 | 686 | 791 | Accepted archival gaps |
| `website_sentences.files` | public | 309 | 374 | 374 | 0 | OK |
| `website_securities.files` | public | 35 | 35 | 35 | 0 | OK |

Remaining missing `letters.files` sample:

```text
k2/20240711d94ba878fe8d11d1c9ca0f20d33445c5/01J2GNWTCDBTCPWWZ48TT9BVM9.pdf
```

Remaining missing `website_leads.files` entries are archived contract-analysis
uploads under `umowy-do-analizy`. These were accepted as non-blocking archival
gaps.

## Conclusion

Data parity is accepted for the imported functional modules. The only count
difference in operational data is a deliberate removal of two orphan
`contact_letter` rows. Remaining storage gaps are accepted legacy/archival
exceptions and do not block parity closure.
