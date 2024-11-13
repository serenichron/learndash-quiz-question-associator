# Changelog
All notable changes to the LearnDash Quiz Question Associator plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.5] - 2024-11-13
### Added
- Proper class-based plugin architecture
- WP_Filesystem integration for secure file operations
- Caching layer for database queries
- Comprehensive error handling and validation
- Internationalization support
- Security improvements for file uploads

### Changed
- Refactored into separate class files for better organization
- Improved error messages and user feedback
- Updated to use WordPress coding standards
- Enhanced CSV processing with better error checking
- Switched from direct file operations to WP_Filesystem
- Improved database operations with caching

### Fixed
- Security issues with unescaped output
- Direct database queries without caching
- Unsanitized file inputs
- File system operation security
- WordPress coding standards violations

## [1.0.3] - 2024-11-13
### Added
- Comprehensive debug logging system
- Debug information display in UI when WP_DEBUG is enabled
- Row number tracking in all messages
- Detailed metadata logging before and after updates
- Database update result tracking
- Improved error reporting with specific failure reasons

### Changed
- Updated UI instructions to better match CSV format
- Improved error message formatting
- Enhanced validation messaging

## [1.0.2] - 2024-11-13
### Added
- Better post verification system
- Specific error messages for missing posts or wrong post types
- Improved success/error message formatting
- Bullet point formatting for messages

### Changed
- Updated CSV processing to handle row-by-row format
- Revised UI instructions to match actual CSV format

## [1.0.1] - 2024-11-13
### Changed
- Fixed CSV processing to handle correct column format
- Updated example format in UI
- Improved error reporting

## [1.0.0] - 2024-11-13
### Added
- Initial release
- CSV upload functionality
- Support for bulk association of questions to quizzes
- Basic validation of quiz and question IDs
- Error handling and success reporting
- Integration with LearnDash pro quiz tables
- Support for LearnDash metadata relationships
- Admin interface under LearnDash LMS menu

## Developer Notes
- Created by: Vlad Tudorie
- Company: Serenichron
- Initial development completed: November 13, 2024
- Tested with WordPress 6.4
- Tested with LearnDash 4.x