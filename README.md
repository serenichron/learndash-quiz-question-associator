# LearnDash Quiz Question Associator

A WordPress plugin that simplifies the process of associating existing LearnDash questions with quizzes through CSV upload.

## Description

This plugin adds a new tool to your LearnDash LMS installation that allows you to bulk associate questions with quizzes using a simple CSV file. It's particularly useful when you need to reorganize or reassign multiple questions across different quizzes.

## Features

- Simple CSV upload interface
- Bulk association of questions to quizzes
- Validation of all quiz and question IDs
- Proper handling of all LearnDash relationships and metadata
- Clear success/error reporting
- Maintains all existing LearnDash data structures

## Installation

1. Download the plugin files
2. Create a new folder 'learndash-quiz-question-associator' in your `/wp-content/plugins/` directory
3. Upload the plugin files to this folder
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Look for 'Quiz Question Associator' under the LearnDash LMS menu

## Usage

1. Navigate to LearnDash LMS > Quiz Question Associator in your WordPress admin
2. Prepare a CSV file with one Quiz ID and one Question ID per row:
   - First column: Quiz ID
   - Second column: Question ID to be associated with the quiz
3. Upload your CSV file
4. Click "Process CSV"
5. Review the results

### CSV Format Example
```
1306846,1306847
1306849,1306850
1307452,1307453
```

Each row represents one quiz-question association.

### Debug Mode

When WP_DEBUG is enabled in wp-config.php, the plugin provides detailed information about:
- CSV processing
- Post verification
- Metadata updates
- Database operations

Debug information appears in:
- WordPress debug log file
- UI (when WP_DEBUG is enabled)

## Requirements

- WordPress 5.0 or higher
- LearnDash LMS 3.0 or higher
- PHP 7.2 or higher
- WP_DEBUG enabled (optional, for detailed logging)

## Support

For support or feature requests, please contact [vlad@serenichron.com](mailto:vlad@serenichron.com)

## Developer

Developed by Vlad Tudorie at Serenichron.

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2024 Serenichron

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```
