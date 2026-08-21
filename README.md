# QuizWhizAI

QuizWhizAI is a PHP and MySQL quiz application that uses the Google Gemini API to generate multiple-choice quizzes. It also includes an offline reading library where users can upload PDF or EPUB books with cover images and read them through the website.

## Features

- User registration and login
- Demo student account created automatically
- AI-generated quizzes using Google Gemini
- Topic-based quiz generation
- Difficulty levels: Beginner, Intermediate, and Advanced
- Question counts: 5, 10, or 15
- Validation for duplicate questions and duplicate options
- Detailed results with:
  - Score and percentage
  - Grade
  - Correct and incorrect answers
  - User answer and correct answer
  - Explanation for each answer
- Dashboard analytics:
  - Total quizzes
  - Average score
  - Best score
  - Strongest topic
  - Topic needing practice
  - Score chart over time
  - Recent quiz review links
- Offline book library
- Upload PDF and EPUB books
- Upload JPG, PNG, or WebP cover images
- Remove uploaded books and covers
- In-browser PDF reading
- Local EPUB chapter reader with Previous and Next navigation
- Fallback to the Offline Books page when quiz generation fails

## Requirements

- Windows
- XAMPP
- Apache
- MySQL
- PHP 7.4 or newer recommended
- PHP extensions:
  - mysqli
  - curl
  - fileinfo
  - mbstring recommended
- Google Gemini API key for AI quiz generation
- `tar.exe` available on Windows for EPUB validation and chapter extraction

## Project Setup

### 1. Install XAMPP

Install XAMPP and start the following services from the XAMPP Control Panel:

- Apache
- MySQL

### 2. Place the project in htdocs

The expected project location is:

```text
C:\xampp\htdocs\projectai-quiz
```

If the folder has another name, update the URL accordingly.

### 3. Open the project

Visit this URL in Google Chrome:

```text
http://localhost/projectai-quiz/
```

The project redirects to the login page.

### 4. Configure the Gemini API key

Create the local configuration file if it does not exist:

```text
includes/config.local.php
```

Add your own API key:

```php
<?php
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY');
define('GEMINI_MODEL', 'gemini-3.5-flash-lite');
```

Do not commit this file to GitHub. It is ignored by `.gitignore` because it contains a secret.

If an API key has ever been exposed publicly, revoke it in Google AI Studio and create a replacement key.

### 5. Database setup

The application connects to this database:

```text
quizai_basic
```

The database and required tables are created automatically when the application first connects to MySQL.

The default MySQL settings are defined in `includes/bootstrap.php`:

```text
Host: localhost
User: root
Password: empty
Database: quizai_basic
```

If your MySQL password is not empty, update the database variables in `includes/bootstrap.php`.

You can also import `sql/schema.sql` through phpMyAdmin. The schema contains these tables:

- `users`
- `quiz_attempts`
- `quiz_questions`
- `quiz_answers`

## Demo Login

The application creates this demo account automatically if it does not already exist:

```text
Email: student@test.com
Password: 12345
```

Change or remove this account before using the project in a real production environment.

## Main Pages

| Page | Purpose |
| --- | --- |
| `index.php` | Redirects visitors to login |
| `login.php` | User login and logout |
| `register.php` | Create a new account |
| `dashboard.php` | Analytics and recent quiz attempts |
| `take-quiz.php` | Generate and answer quizzes |
| `results.php` | Review a completed quiz |
| `books.php` | Upload, read, and remove offline books |
| `reader.php` | Open a book in the website reader |
| `epub-chapter.php` | Extract and display an EPUB chapter |

## Using Quiz Generation

1. Log in.
2. Open **Take Quiz**.
3. Enter a topic.
4. Select a difficulty.
5. Select 5, 10, or 15 questions.
6. Click **Generate quiz**.
7. Answer every question.
8. Submit the quiz.
9. Review the score, grade, explanations, and answers.

Generated questions must contain four unique options and a valid correct-answer index. Invalid or incomplete Gemini responses are rejected.

If Gemini cannot generate a quiz, the application redirects to the Offline Books page and displays a fallback message.

## Using the Offline Book Library

1. Open **Offline Books** from the dashboard sidebar.
2. Enter a book title.
3. Select a PDF or EPUB file.
4. Select a JPG, PNG, or WebP cover image.
5. Click **Add book**.
6. Click **Read book** to open it in the website reader.

Upload limits:

- PDF or EPUB: 20 MB maximum
- Cover image: 5 MB maximum

Uploaded files are stored locally:

```text
uploads/books/
uploads/covers/
```

Book metadata is stored in:

```text
uploads/library.json
```

Uploaded files are not stored in MySQL.

### EPUB requirements

The EPUB must be a complete, valid EPUB archive. The application checks the archive before saving it. Corrupted or incomplete EPUB files are rejected.

EPUB reading uses the local chapter reader. It extracts HTML, XHTML, or HTM chapters from the EPUB archive using Windows `tar.exe`.

### Removing a book

Only uploaded books have a **Remove** button. Removing a book deletes:

- The catalog entry
- The uploaded PDF or EPUB file
- The uploaded cover image

## Database Design

### `users`

Stores registered users and password hashes.

### `quiz_attempts`

Stores the topic, score, total questions, user, and completion time.

### `quiz_questions`

Stores every generated question, its options, the correct option, and its explanation for a particular attempt.

### `quiz_answers`

Stores the answer selected by the user and whether it was correct.

Older quiz attempts created before detailed result storage may show a score but not a question-by-question review.

## Troubleshooting

### Apache does not start

- Check whether another program is using port 80 or 443.
- Close other local web servers.
- Run XAMPP with the required permissions.
- Try `http://localhost/projectai-quiz/` after Apache starts.

### MySQL does not start

- Start MySQL from the XAMPP Control Panel.
- Open phpMyAdmin at `http://localhost/phpmyadmin/`.
- Confirm that the `quizai_basic` database exists.

### API quiz generation fails

Check the following:

- `includes/config.local.php` exists.
- The API key is valid and has not been revoked.
- The configured Gemini model is available to your key.
- PHP cURL is enabled.
- Apache can make outbound HTTPS requests.

When generation fails, the application sends the user to Offline Books instead of showing a basic fake quiz.

### Uploaded book does not open

- Confirm that the book appears in `uploads/library.json`.
- Confirm that the referenced file exists in `uploads/books/`.
- Re-upload the file if the EPUB was reported as incomplete or corrupted.
- Ensure Apache has permission to read the uploads folders.
- For EPUBs, confirm that Windows `tar.exe` is available.

### A page shows old styling

Use a hard refresh in Chrome:

```text
Ctrl + F5
```

## Git and GitHub

Check the current state:

```powershell
git status
```

Stage changes:

```powershell
git add .
```

Review staged changes without opening the pager:

```powershell
git --no-pager diff --cached
```

Create a commit:

```powershell
git commit -m "Describe the changes"
```

Push to the main branch:

```powershell
git push origin main
```

Never add `includes/config.local.php` or private uploaded files containing sensitive material to a public repository.

## Security Notes

This project is designed for local learning and development. Before production use, add:

- CSRF protection for forms
- A non-root MySQL account
- Secure session cookie settings
- API rate limiting
- Request timeouts and stronger error logging
- Authorization for administrative actions
- Upload malware scanning
- Database foreign keys and cascading rules
- HTTPS

Never expose or commit Gemini API keys, database passwords, or user passwords.
