# Display Condition On Request Var

A WordPress plugin that adds conditional display capabilities to Elementor based on request variables and taxonomy terms.

## Description

This plugin extends Elementor with conditional display functionality, allowing you to show or hide containers based on:
- URL parameters (request variables)
- Taxonomy terms (specifically the 'domein' taxonomy)
- Custom matching conditions

## Features

- **Domain-based Conditional Display**: Show/hide Elementor containers based on domain taxonomy terms
- **Request Variable Matching**: Conditional display based on URL parameters
- **Multiple Condition Types**: Equals, not equals, contains, not contains
- **Elementor Integration**: Seamless integration with Elementor's interface
- **Custom Widget**: Includes a conditional widget for advanced use cases

## Installation

1. Upload the plugin files to the `/wp-content/plugins/display-condition-on-request-var/` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Make sure Elementor is installed and activated

## Requirements

- WordPress 5.0 or higher
- Elementor 3.0.0 or higher
- PHP 7.0 or higher

## Usage

### Container Conditional Display

1. Edit any page with Elementor
2. Select a Container element
3. Go to the Layout section in the left panel
4. Enable "Use Match Domain Condition"
5. Select the domain value to match against
6. The container will only display when the condition is met

### Conditional Widget

1. Add the "Conditional Widget" to your page
2. Configure the content to display
3. Set up the condition parameters:
   - Request Variable: The URL parameter name to check
   - Expected Value: The value to match against
   - Condition Type: How to compare the values

## How It Works

The plugin checks for domain values in this order:
1. Current post's 'domein' taxonomy term
2. URL parameter 'domein'

For the special value 'algemeen', the content is shown when no domain is specified.

## Development

This plugin follows WordPress coding standards and best practices.

### File Structure

```
display-condition-on-request-var/
├── display-condition-on-request-var.php  # Main plugin file
├── widgets/
│   └── conditional-widget.php            # Conditional widget implementation
├── README.md                             # This file
├── CHANGELOG.md                          # Version history
└── .gitignore                            # Git ignore rules
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## Author

**Bart ter Morshuizen**
- GitHub: [@barttermorshuizen](https://github.com/barttermorshuizen)

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support and feature requests, please use the GitHub issues page.
