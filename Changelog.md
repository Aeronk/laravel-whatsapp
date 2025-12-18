# Changelog

All notable changes to `laravel-whatsapp` will be documented in this file.

## [1.0.0] - 2024-12-18

### Added
- Initial release
- Meta WhatsApp Cloud API integration
- Comprehensive message sending capabilities (text, image, video, audio, document, location)
- Interactive message support (buttons, lists)
- WhatsApp Flows v7.3+ support with form builder
- Webhook handling for incoming messages and status updates
- Database models for messages, users, sessions, and flows
- Chatbot engine with rule-based logic
- AI integration with OpenAI and Google Gemini
- Session management with automatic expiry
- Event-driven architecture (MessageReceived, MessageStatusUpdated, FlowResponseReceived)
- Configurable middleware and rate limiting
- Comprehensive logging
- Laravel facade support
- Full test suite
- Detailed documentation and examples

### Features
- Send text messages with optional reply context
- Send media messages (images, videos, audio, documents)
- Send location messages
- Send interactive buttons and lists
- Create and send WhatsApp Flows
- Process incoming webhooks
- Track message status (sent, delivered, read, failed)
- Manage user sessions with context storage
- AI-powered responses with conversation history
- Block/unblock users
- Message retention policies
- Extensible chatbot rules engine

### Development
- PSR-4 autoloading
- PHPUnit test suite
- GitHub Actions CI/CD ready
- Composer package ready

## [Unreleased]

### Planned Features
- Template message support
- Media download from WhatsApp servers
- Flow response handling and parsing
- Broadcast messaging with queues
- Message scheduling
- Analytics and reporting
- Multi-language support helper
- Rate limiting per user
- Message templates manager
- Flow visual builder
- Admin panel integration
- Message search and filtering
- Export conversation history
- Webhook signature verification
- Payment integration support

