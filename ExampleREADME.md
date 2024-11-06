
# Personal Task Manager

## Project Overview
The Personal Task Manager is a full-stack application allowing users to manage personal tasks. It includes features for user registration, task creation, viewing, editing, and analytics. The application demonstrates secure user authentication with JWT, RESTful API development, data storage, front-end design, and database integration.

## Features
- **User Authentication**: Registration, login, and secure session management.
- **Task Management**: CRUD operations for tasks, including filtering and status updates.
- **Task Analytics**: Dashboard with task statistics (total, completed, pending) displayed with visual charts.
- **Additional Functionalities**:
  - Search by task title
  - Pagination for task lists
  - Client-side and server-side validations

## Technology Stack
- **Front-End**: React, Axios, CSS (or Bootstrap/Tailwind), Feature folder structure.
- **Back-End**: Laravel 11, PHP, Domain-Driven Design (DDD) for clear domain logic.
- **Authentication**: JSON Web Tokens (JWT).
- **Database**: PostgreSQL for relational data storage.
- **DevOps**: Docker for containerization.

## Project Setup

### Prerequisites
- Docker and Docker Compose
- Node.js and npm
- Composer (for Laravel dependencies)

### Installation Instructions
1. **Clone the Repository**:
   ```bash
   git clone https://github.com/RasimAghayev/AC-task.git
   cd AC-task
   ```

2. **Set up Environment Variables**:
   - Copy `.env.example` to `.env`.
   - Configure necessary variables:
     - Database settings (PostgreSQL credentials)
     - JWT secret for token authentication
   ```bash
   cd src/be/ && cp .env.example .env
   ```

3. **Install Back-End Dependencies**:
   ```bash
   docker-compose run --rm composer install
   ```

4. **Run Database Migrations and Seed Data**:
   ```bash
   php artisan migrate --seed
   ```

5. **Build Front-End**:
   ```bash
   docker-compose run --rm npm install
   docker-compose run --rm npm run dev
   ```

6. **Start the Application with Docker**:
   - In the project root, start Docker to set up the application environment.
   ```bash
   docker-compose up --build
   ```

7. **Access the Application**:
   - Front-End: `http://localhost:5173`
   - API Documentation (if available): `http://localhost/api/documentation`

## API Endpoints

| Method | Endpoint               | Description                         |
|--------|------------------------|-------------------------------------|
| POST   | /api/register          | Register a new user                 |
| POST   | /api/login             | Log in and receive JWT              |
| GET    | /api/tasks             | Get all tasks for the logged-in user|
| POST   | /api/tasks             | Create a new task                   |
| PUT    | /api/tasks/:id         | Update an existing task             |
| PATCH  | /api/tasks/:id         | Update specific an existing task    |
| DELETE | /api/tasks/:id         | Delete a task by ID                 |
| GET    | /api/tasks/reports     | Get task statistics                 |

### Example Requests/Responses
#### Register a New User
```json
POST /api/register
{
  "email": "user@example.com",
  "username": "username",
  "password": "password123"
}
```

#### Login user
```json
POST /api/login
{
  "email": "user@example.com",
  "password": "password123"
}
```

#### Get All Tasks
```json
GET /api/tasks
Headers: { "Authorization": "Bearer <JWT Token>" }
Response: [
  {
    "id": 1,
    "title": "Sample Task",
    "description": "Description of task",
    "due_date": "YYYY-MM-DD",
    "status": "Pending"
  }
]
```


#### Get ID Tasks
```json
GET /api/tasks/:id
Headers: { "Authorization": "Bearer <JWT Token>" }
Response:{
    "id": 1,
    "title": "Sample Task",
    "description": "Description of task",
    "due_date": "YYYY-MM-DD",
    "status": "Pending"
  }
```
## Folder Structure
This project follows the **Feature Folder Structure** for organization.

- **backend**: Laravel services and database schemas.
- **frontend**: React components, hooks, and state management.
- **docker**: Docker and Docker Compose files for containerized deployment.

## Additional Information

### Task Analytics
Provides a summary of tasks, visualized with a Pie or Bar chart to show:
- Total tasks
- Completed tasks
- Pending tasks

### Code Quality and Security
- ESLint for front-end code linting.
- Proper error handling and validation checks for back-end services.
- Security policies implemented with role-based access control.

### Known Issues
If any known issues or limitations are present, list them here.

## Contributing
Feel free to submit issues and pull requests.

## License
This project is open-source under the MIT License.
