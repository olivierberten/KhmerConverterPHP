# KhmerConverterPHP
These scripts transcode strings from Legacy khmer fonts to Unicode and vice versa. You can see them in action at http://www.selapa.net/khmerfonts/
## How does it work?
### Legacy → Unicode
1. Search and replace from the database
  1. Recompose characters
  2. Transcode other characters
    * Ligatures get separated into characters
    * Ornaments get enclosed between 0x91 and 0x92
    * Khmer characters missing in Unicode get enclosed between 0x86 and 0x87
    * Characters missing in the legacy font get enclosed between 0x96 and 0x97
2. Reorder characters according to Unicode order  
  *This code is translated to PHP from KhmerOS [khmerconverter](http://www.khmeros.info/en/khmer-converter) Python software*

### Unicode → Legacy
1. Reorder characters according to visual order  
  *This code is translated to PHP from KhmerOS [khmerconverter](http://www.khmeros.info/en/khmer-converter) Python software*
2. Search and replace from the database
  1. Transcode characters
  2. Decompose composite characters if necessary
    * Missing characters get enclosed between 0x96 and 0x97
  3. Apply ligatures if present in the font

## TODO
* Refine the database (some font mappings aren't yet correct)
* Word-breaking
* Transcode documents with multiple fonts
