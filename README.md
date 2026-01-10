# Global News Network

A news portal website built with PHP and MySQL, designed to deliver breaking news, featured articles, and content.

## Tech Stack
- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 8.2
- **Database:** MySQL 8.0
- **Containerization:** Docker & Docker Compose

## Project Structure
```text
GlobalNewsNetwork/
├── src/                # Source code (PHP, HTML, CSS, JS)
│   ├── backend/        # Backend logic and classes
│   ├── database/       # SQL schema and initial data
│   ├── frontend/       # Static assets and templates
│   └── assets/         # Images and icons
├── docs/
│   ├── screenshots/
│   └── notes.md
├── Dockerfile          # Docker configuration for PHP app
├── docker-compose.yml  # Multi-container setup
├── .gitignore          # Git ignore rules
└── .dockerignore       # Docker ignore rules
```

## How to Run with Docker

### Prerequisites
- Docker installed on your machine
- Docker Compose installed

### Steps to Run
1. **Clone the repository:**
   ```bash
   git clone <your-repo-url>
   cd GlobalNewsNetwork
   ```

2. **Build and start the containers:**
   ```bash
   docker-compose up --build
   ```

3. **Access the application:**
   - Open your browser and go to `http://localhost:8080`
   - The database will be automatically initialized with sample data.

4. **Stop the application:**
   ```bash
   docker-compose down
   ```

## Configuration
The application uses environment variables for database connection, which are managed in `docker-compose.yml`:
- `DB_HOST`: db
- `DB_NAME`: global_news_network
- `DB_USER`: root
- `DB_PASSWORD`: root_password

## Features
- User Registration and Login
- Article Management (Admin)
- Category-based News Filtering
- Featured and Breaking News Sections
- Search Functionality

## Steps to upload on a vps
1. Update the system

2. Download docker & docker compose

3. Clone Github Repo

``cd /opt
git clone https://github.com/MohanadHmaid/GlobalNewsNetwork.git
cd repo``

4. Creating a docker compose file with MySQL

5. Test The DB and the project

## Production URL 
Here the url where in Back4App gave me https://globalnewsnetwork-6a775l51.b4a.run/