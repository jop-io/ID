# ID
PHP class to generate identification numbers and strings. IDs are generated  with a control character using Luhn mod N, to enable verification of IDs programmatically.

# Pool types
| Name | Radix | Characters |
|---|---|---|
| alphanum | 62 | a-z, A-Z, 0-9  |
| alpha | 52 | a-z, A-Z |
| lower | 26 | a-z |
| upper | 26 | A-Z |
| numeric | 10 | 0-9 |
| nozero | 9 | 1-9 |
| safe&ast; | 29 |  1-9, BCDFGHJKLMNPQRSTVWXZ |

*__&ast;__ Safe means that vowels has been removed to prevent the risk of inappropriate words  being generated within the ID. All letters are in uppercase to ease reading for humans. The digit "0" (zero) has also been removed to prevent confusion with the letter "O".*

# Usage
```php
require_once 'ID.php';
$id = new ID();

print $id->Generate();
```

