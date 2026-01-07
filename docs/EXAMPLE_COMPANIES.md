# Example Companies - Identification Numbers

This document contains example companies from the BIR11 system with their identification numbers (REGON, NIP, KRS).

## Company 1: GŁÓWNY URZĄD STATYSTYCZNY (Main Statistical Office)

- **REGON**: 000331501
- **NIP**: 5261040828
- **KRS**: Not provided
- **Type**: Legal entity (P)
- **Location**: ul. Test-Krucza 208, 00-925 Warszawa

## Company 2: "EUROPEJSKA" SPÓŁKA Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ

- **REGON**: 276592498
- **NIP**: 9542300619
- **KRS**: 0000135115
- **Type**: Limited liability company
- **Location**: ul. Test-Krucza 50, 02-672 Warszawa
- **Business**: Pharmaceutical retail and production

### Local Units:
1. **APTEKA "EUROPEJSKA"**
   - REGON14: 27659249800038
   - Location: ul. Test-Wilcza 7, 40-026 Katowice

2. **APTEKA "EUROPEJSKA" (GEANT)**
   - REGON14: 27659249800045
   - Location: ul. Test-Wilcza 60, 40-028 Katowice

## Company 3: Anonymized Company (AAAAAAAA)

- **REGON**: xxxxxxxxx (anonymized)
- **NIP**: nnnnnnnnnn (anonymized)
- **KRS**: Not provided
- **Type**: Natural person (F)
- **Location**: ul. Test-Wilcza yy, 23-200 Kraśnik

## Company 4: GOSPODARSTWO ROLNE (Farm)

- **REGON**: xxxxxxxxx (anonymized - same as Company 3)
- **NIP**: nnnnnnnnnn (anonymized - same as Company 3)
- **KRS**: Not provided
- **Type**: Natural person (F)
- **Location**: Sulów zz, 23-213 Zakrzówek

## Company 5: MIKOŁAJ XXXXXXXX "MELISSA" (Natural Person Business)

- **REGON**: yyyyyyyyy (anonymized)
- **NIP**: Not shown in excerpt
- **KRS**: Not applicable (natural person)
- **Type**: Natural person conducting business activity

## Company 6: TEST-ZIELIŃSKI ZBIGNIEW (Natural Person Business)

- **REGON**: xxxxxxxxx (anonymized)
- **NIP**: nnnnnnnnnn (anonymized)
- **KRS**: Not applicable (natural person)
- **Type**: Natural person conducting business activity

## Notes

### Identification Number Types

- **REGON**: Statistical identification number (9 or 14 digits)
  - 9 digits: Main entity
  - 14 digits: Local unit
- **NIP**: Tax Identification Number (10 digits)
- **KRS**: National Court Register number (applicable only to legal entities)

### Data Anonymization

Many examples in the source file use placeholder values:
- `xxxxxxxxx` for REGON numbers
- `nnnnnnnnnn` for NIP numbers
- `yy`, `zz` for property numbers

### Complete Examples

Only two companies have complete, non-anonymized data:
1. **GŁÓWNY URZĄD STATYSTYCZNY** (Main Statistical Office)
2. **"EUROPEJSKA" SPÓŁKA Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ**

These can be used for testing and development purposes.

## Usage in Testing

When testing the GUS REGON integration:
- Use REGON **000331501** or **276592498** for valid lookups
- Use NIP **5261040828** or **9542300619** for valid lookups
- Note that actual API responses may vary from these example structures
