# Poll & Survey Builder

## Project Overview

Poll & Survey Builder is a web programming project that allows users to create multi-question polls, share voting links, collect responses, and view results in a clear visual format.

The system supports single-choice and multiple-choice questions, stores all data in a MySQL database, and displays vote results with counts and percentages.

## Features

- Create a new poll with a title and description.
- Add multiple questions to each poll.
- Support single-choice and multiple-choice questions.
- Add and remove answer options dynamically.
- Save polls to a MySQL database.
- Generate a voting page for each poll.
- Submit votes using JavaScript Fetch API.
- Prevent duplicate voting using a voter token.
- View poll results with vote counts and percentages.
- Display results using visual progress bars.
- Manage created polls from an admin page.
- Delete polls from the admin page.
- Responsive design tested on mobile, tablet, and desktop screen sizes.

## Technologies Used

- HTML
- CSS
- JavaScript
- PHP
- MySQL
- XAMPP
- phpMyAdmin

## Project Structure

poll-survey-builder/
│
├── api/
│   ├── delete_poll.php
│   ├── get_results.php
│   ├── save_poll.php
│   └── submit_vote.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── create.js
│       ├── results.js
│       └── vote.js
│
├── config/
│   └── database.php
│
├── database/
│   └── schema.sql
│
├── includes/
│   ├── footer.php
│   ├── functions.php
│   └── header.php
│
├── admin.php
├── create.php
├── index.php
├── results.php
├── vote.php
├── README.md
└── reflection.txt

## Database Setup

1. Open XAMPP.
2. Start Apache and MySQL.
3. Open phpMyAdmin.
4. Create a database named: poll_survey_builder
5. Import the SQL file located at: database/schema.sql
6. Make sure the database connection settings in config/database.php match your local XAMPP setup.

Default database configuration:

Host: 127.0.0.1
Port: 3307
Database: poll_survey_builder
Username: root
Password: empty

## How to Run the Project

1. Copy the project folder into the XAMPP htdocs directory.

Example path:

C:\xampp\htdocs\poll-survey-builder

2. Start Apache and MySQL from XAMPP.

3. Open the project in the browser:

http://localhost/poll-survey-builder/

## Main Pages

### Home Page

File: index.php

The home page introduces the project and provides navigation to create and manage polls.

### Create Poll Page

File: create.php

This page allows the user to create a poll, add questions, choose question types, and add answer options.

### Vote Page

Example:

vote.php?id=1

This page allows users to answer the poll questions and submit their vote.

### Results Page

Example:

results.php?id=1

This page displays poll results using vote counts, percentages, and progress bars.

### Manage Polls Page

File: admin.php

This page allows the user to view created polls, open voting pages, view results, and delete polls.

## API Endpoints

### Save Poll

File: api/save_poll.php

Receives poll data as JSON and saves the poll, questions, and options to the database.

### Submit Vote

File: api/submit_vote.php

Receives vote data as JSON and saves the selected answers.

### Get Results

Example:

api/get_results.php?id=1

Returns poll results as JSON, including questions, options, vote counts, and percentages.

### Delete Poll

File: api/delete_poll.php

Deletes a poll and its related questions, options, and votes.

## Responsive Design Testing

The project was tested on three screen sizes:

- Mobile: 375px
- Tablet: 768px
- Desktop: Full laptop screen

Tested pages:

- Home page
- Create Poll page
- Vote page
- Results page
- Manage Polls page

All pages were tested to make sure the layout remains clear, readable, and usable on different screen sizes.

## Testing Summary

The following functionality was tested successfully:

- Database connection
- Poll creation
- Dynamic question creation
- Dynamic option creation
- Removing questions
- Removing options
- Saving polls to the database
- Opening voting links
- Submitting votes
- Preventing duplicate voting
- Viewing results
- Displaying vote counts and percentages
- Returning results as JSON from the API
- Deleting polls
- Responsive design on mobile, tablet, and desktop

## Important Notes

- The current version focuses on the main project requirements: creating polls, voting, viewing results, managing polls, database storage, APIs, and responsive design.
- The Manage Polls page is currently open without an admin password because authentication was not required in the main project scope.
- Admin password and session protection can be added later if required by the instructor.
- The project runs locally using XAMPP.

## Future Enhancements

- Add admin login and session protection.
- Add poll editing functionality.
- Add search and filter options in the Manage Polls page.
- Add charts for results visualization.
- Add export results feature.
- Improve accessibility and keyboard navigation.

## Conclusion

This project demonstrates a complete poll and survey system using PHP, MySQL, JavaScript, HTML, and CSS. It includes dynamic poll creation, database integration, vote submission, result calculation, responsive design, API endpoints, and admin management features.