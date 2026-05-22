# Shop Mode

Shop mode prepares a database instance configured with E-commerce schemas and seed fixtures.

## Modules Set

Shop mode ensures that the following modules are loaded and active:
- `main`
- `iblock`
- `catalog`
- `sale`
- `highloadblock`
- `fileman`
- `location`
- `perform`
- `security`
- `rest`
- `seo`
- `ui`
- `search`

## Dynamic Schema

It executes dedicated schema files (`resources/schema/sqlite-shop.sql`) which replicate catalog/sale tables in SQLite-compatible DDL.

## Import XML

If `eshop_import_demo_xml` is enabled, the wizard parser reads standard CommerceML data formats from the Core directory and populates catalog items, prices, currencies, and products deterministically.
