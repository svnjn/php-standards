# Valid

```php
use Svnjn\Standards\Internal\ArrayReader;

$input = ArrayReader::fromJson('{"id": "inv_1"}');
$id = $input->string('id');
```

<!-- docs-check: skip -->
```php
$this->would->not(parse
```
