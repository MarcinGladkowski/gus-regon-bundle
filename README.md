# GUS REGON Bundle for Symfony

A Symfony bundle for integrating with the Polish GUS (Central Statistical Office) REGON API. This bundle provides easy access to business registry data for Polish companies.

## Features

- 🔍 **Business Lookup**: Search for companies by NIP (Tax ID) or REGON number
- 📊 **Complete Data**: Retrieve comprehensive business information including addresses, PKD codes, and more
- ✅ **Validation**: Built-in NIP and REGON number validators
- 🚀 **Easy Integration**: Simple Symfony service integration
- 💾 **Caching**: Optional caching support for API responses
- 🔒 **Type Safety**: Full PHP 8.2+ type declarations and DTOs

## Requirements

- PHP >= 8.2
- Symfony >= 7.0
- GUS API credentials (user key)

## Installation

```bash
composer require marcingladkowski/gus-regon-bundle
```

## Configuration

Register the bundle in your `config/bundles.php`:

```php
return [
    // ...
    GusBundle\GusBundle::class => ['all' => true],
];
```

Configure your GUS API credentials in `config/packages/gus.yaml`:

```yaml
gus:
    api_key: '%env(GUS_API_KEY)%'
    environment: 'production' # or 'test'
    cache:
        enabled: true
        ttl: 3600
```

Set your API key in `.env`:

```env
GUS_API_KEY=your_gus_api_key_here
```

## Usage

### Basic Company Lookup

```php
use GusBundle\Service\GusApiClient;

class YourController
{
    public function __construct(
        private GusApiClient $gusClient
    ) {}

    public function lookup(string $nip): Response
    {
        $businessData = $this->gusClient->searchByNip($nip);
        
        if ($businessData) {
            // Access company data
            $name = $businessData->getName();
            $regon = $businessData->getRegon();
            $address = $businessData->getAddress();
            
            // ...
        }
    }
}
```

## Data Transfer Objects

The bundle provides comprehensive DTOs for structured data:

- `BusinessDataDTO`: Main business entity data
- `AddressDTO`: Address information
- `PkdCodeDTO`: PKD (Polish Classification of Activities) codes

## Testing

```bash
composer install
vendor/bin/phpunit
```

## Documentation

For detailed documentation, see the [docs](docs/) directory:

- [Example Companies Data](docs/EXAMPLE_COMPANIES.md)
- [Contributing Guide](CONTRIBUTING.md)

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Credits

Created by [Marcin Gladkowski](https://github.com/MarcinGladkowski)

Uses the [gusapi/gusapi](https://github.com/johnzuk/GusApi) library for GUS API communication.

## Support

- 🐛 [Report Issues](https://github.com/MarcinGladkowski/gus-regon-bundle/issues)
- 💬 [Discussions](https://github.com/MarcinGladkowski/gus-regon-bundle/discussions)

