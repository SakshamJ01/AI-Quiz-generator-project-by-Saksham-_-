# QuizAI Runnable Website

This workspace contains a PHP/MySQL version of the QuizAI design system.

## Included

- Landing page
- Login, sign up, forgot password, reset password, and email verification pages
- Learner dashboard
- Quiz creation and taking flow
- Results, history, and profile pages
- Admin dashboard, users, categories, and settings pages
- Shared CSS and jQuery
- MySQL schema plus PHP bootstrap that can create tables when a DB connection exists

## Local setup with XAMPP

1. Put the project folder inside `htdocs` or point Apache to this folder.
2. Start Apache and MySQL in XAMPP.
3. Create a database named `quizai`.
4. Import `sql/schema.sql` if you want the structure created manually.
5. Open the site in your browser.

## Demo login

- Admin: `admin@quizai.test`
- Learner: `learner@quizai.test`
- Password: `password123`

## Notes

- The PHP bootstrap also creates tables if the database connection succeeds and the schema is missing.
- The app falls back to demo data for read-only screens when the database is unavailable.
