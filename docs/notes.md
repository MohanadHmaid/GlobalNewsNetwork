# Docker News Network Assignmet Notes 

## Biggest Docker Problem Faced
The hard part was how the PHP application could communicate with the MySQL database container, the application was configured to connect to `localhost`, which refers to the container itself not the db container.

**Solution:**
I implemented environment variables using `getenv()` in the `Database` class. By setting `DB_HOST` to `db` (the service name in `docker-compose.yml`), Docker's internal DNS resolver correctly routes the traffic to the database container.

## Most Important Git/GitHub Lesson
I learned the value of a clean repository structure and the use of `.gitignore` and `.dockerignore`. Keeping  sensitive data (like database volumes) out of the repository ensures a professional and secure workflow.

## Bonus Tasks Implemented
- **Docker Compose:** Added `docker-compose.yml` for easy multi-container orchestration.
- **Multi-stage Build (Optional):** While not strictly necessary for this PHP app, the Dockerfile is optimized for production.
- **Healthcheck:** Added to ensure the database is ready before the app starts (via `depends_on`).

## User authinicatoin
The default value for the admin user is 'admin' 'admin'