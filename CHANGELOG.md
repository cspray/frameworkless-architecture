# Changelog

## v0.1.0

This release is an initial commit holding a fair amount of critical functionality

### Added

- Provide a simple configuration system that allow developers to provide 
environment specific configuration values.
- Allow developer to define HTTP routes that the app can execute.
- Provide a PSR-15 implementation that allows the composing of PHP Middleware
to handle HTTP Request/Response.
- Provide Models that validate and interact with persistence abstraction to 
handle working with Entities.
- Provide several Entity objects representing a real life example of dog training
- Provide a bootstrap method that ensures the appropriate object graph and 
initialization procedures are started.
- Provide ability to configure CORS and add a CORS middleware.
