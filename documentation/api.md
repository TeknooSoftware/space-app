# API REST

## Overview

Space provides a RESTful API interface for programmatic access to all platform features. The API uses JWT (JSON Web
Tokens) for authentication and follows standard HTTP conventions.

## Authentication

### API Token Authentication

Space supports API token-based authentication that allows you to generate JWT tokens programmatically without using a
password.

#### Generating an API Token

1. Log in to the Space web interface
2. Navigate to your user settings page
3. Go to the "API Keys" section
4. Click "Add API Key" to generate a new API token
5. Save the token name and value securely - the token value will only be shown once

#### Obtaining a JWT Token

To authenticate with the API, you need to exchange your API token for a JWT token using the login endpoint.

**Endpoint:** `POST /api/v1/login`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "username": "<API_TOKEN_NAME>:<USER_EMAIL>",
  "token": "<API_TOKEN_VALUE>"
}
```

**Parameters:**
- `username`: Combination of your API token name and user email, separated by a colon (`:`)
- `token`: The API token value generated from the UI

**Response:**
```json
{
  "data": {
    "token": "<JWT_TOKEN>"
  }
}
```

**Example using cURL:**
```bash
# Set your credentials
SPACE_API_TOKEN_NAME="my-token"
SPACE_LOGIN="user@example.com"
SPACE_API_TOKEN_VALUE="your-api-token-value"
SPACE_HOSTNAME="space.example.com"

# Get JWT token
JWT_TOKEN=$(curl -s -X POST \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d "{\"username\":\"${SPACE_API_TOKEN_NAME}:${SPACE_LOGIN}\",\"token\":\"${SPACE_API_TOKEN_VALUE}\"}" \
  https://${SPACE_HOSTNAME}/api/v1/login | jq -r '.data.token')

echo "JWT Token: ${JWT_TOKEN}"
```

#### Using the JWT Token

Once you have obtained a JWT token, include it in the `Authorization` header of your API requests:

```
Authorization: Bearer <JWT_TOKEN>
```

**Example API Request:**
```bash
# Create a new job
curl -X POST \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer ${JWT_TOKEN}" \
  -H 'Content-Type: application/json' \
  -d '{"environment":"production"}' \
  https://${SPACE_HOSTNAME}/api/v1/project/${PROJECT_ID}/job/new
```

### JWT Token Configuration

JWT tokens are configured via environment variables:

- `SPACE_JWT_SECRET_KEY`: Path to the private key used to sign JWT tokens
- `SPACE_JWT_PUBLIC_KEY`: Path to the public key used to verify JWT tokens
- `SPACE_JWT_PASSPHRASE`: Passphrase to unlock the private key
- `SPACE_JWT_TTL`: Token time-to-live in seconds
- `SPACE_JWT_MAX_DAYS_TO_TIVE`: Maximum life in days for JWT token
- `SPACE_JWT_ENABLE_IN_QUERY` *(optional)*: Allow JWT token to be passed via query string

## API Endpoints

The API is organized into authenticated user endpoints and admin endpoints.

### User Settings

**Get/Update User Settings**
- `GET /api/v1/my-settings`
- `POST /api/v1/my-settings`
- `PUT /api/v1/my-settings`

**Generate JWT Token** *(for programmatic token generation)*
- `POST /api/v1/jwt/create-token`

### Account Management

**Get/Update Account Settings**
- `GET /api/v1/account/settings`
- `POST /api/v1/account/settings`
- `PUT /api/v1/account/settings`

**Get Account Status**
- `GET /api/v1/account/status`

**Manage Account Environments**
- `GET /api/v1/account/environments`
- `POST /api/v1/account/environments`
- `PUT /api/v1/account/environments`

**Manage Account Variables**
- `GET /api/v1/account/variables`
- `POST /api/v1/account/variables`
- `PUT /api/v1/account/variables`

### Account Cluster Management

**List Account Clusters**
- `GET /api/v1/account/clusters`

**Create Account Cluster**
- `POST /api/v1/account/cluster/new`
- `PUT /api/v1/account/cluster/new`

**Get/Update Account Cluster**
- `GET /api/v1/account/cluster/{id}`
- `POST /api/v1/account/cluster/{id}`
- `PUT /api/v1/account/cluster/{id}`

**Delete Account Cluster**
- `POST /api/v1/account/cluster/{id}/delete`
- `DELETE /api/v1/account/cluster/{id}/delete`

### Project Management

**List Projects**
- `GET /api/v1/projects`
- `POST /api/v1/projects` *(with search/filter)*

**Create Project**
- `POST /api/v1/project/new`
- `PUT /api/v1/project/new`

**Get/Update Project**
- `GET /api/v1/project/{id}`
- `POST /api/v1/project/{id}`
- `PUT /api/v1/project/{id}`

**Manage Project Variables**
- `GET /api/v1/project/{id}/variables`
- `POST /api/v1/project/{id}/variables`
- `PUT /api/v1/project/{id}/variables`

**Refresh Project Credentials**
- `POST /api/v1/project/{id}/refresh-credentials`

**Delete Project**
- `POST /api/v1/project/{id}/delete`
- `DELETE /api/v1/project/{id}/delete`

### Job Management

**Create New Job**
- `POST /api/v1/project/{projectId}/job/new`
- `PUT /api/v1/project/{projectId}/job/new`

**Get Pending Job Status**
- `GET /api/v1/project/{projectId}/job/pending/{newJobId}`

**List Jobs for Project**
- `GET /api/v1/project/{projectId}/jobs`
- `POST /api/v1/project/{projectId}/jobs` *(with search/filter)*

**Get Job Details**
- `GET /api/v1/project/{projectId}/job/{id}`

**Restart Job**
- `POST /api/v1/project/{projectId}/job/{jobId}/restart`

**Delete Job**
- `POST /api/v1/project/{projectId}/job/{id}/delete`
- `DELETE /api/v1/project/{projectId}/job/{id}/delete`

### Admin Endpoints

All admin endpoints are prefixed with `/api/v1/admin`.

#### Admin - Account Management

**List All Accounts**
- `GET /api/v1/admin/accounts`
- `POST /api/v1/admin/accounts` *(with search/filter)*

**Create Account**
- `POST /api/v1/admin/account/new`
- `PUT /api/v1/admin/account/new`

**Get/Update Account**
- `GET /api/v1/admin/account/{id}`
- `POST /api/v1/admin/account/{id}`
- `PUT /api/v1/admin/account/{id}`

**Get Account Status**
- `GET /api/v1/admin/account/{id}/status`

**Manage Account Environments**
- `GET /api/v1/admin/account/{id}/environments`
- `POST /api/v1/admin/account/{id}/environments`
- `PUT /api/v1/admin/account/{id}/environments`

**Manage Account Variables**
- `GET /api/v1/admin/account/{id}/variables`
- `POST /api/v1/admin/account/{id}/variables`
- `PUT /api/v1/admin/account/{id}/variables`

**Delete Account**
- `POST /api/v1/admin/account/{id}/delete`
- `DELETE /api/v1/admin/account/{id}/delete`

**Reinstall Account Registry**
- `POST /api/v1/admin/account/{id}/registry/reinstall`

**Refresh Account Quota**
- `POST /api/v1/admin/account/{id}/quota/refresh`

**Reinstall Account Environment**
- `POST /api/v1/admin/account/{id}/environment/{envName}/{clusterName}/reinstall`

#### Admin - Account Cluster Management

**List Account Clusters**
- `GET /api/v1/admin/account/{accountId}/clusters`

**Create Account Cluster**
- `POST /api/v1/admin/account/{accountId}/cluster/new`
- `PUT /api/v1/admin/account/{accountId}/cluster/new`

**Get/Update Account Cluster**
- `GET /api/v1/admin/account/{accountId}/cluster/{id}`
- `POST /api/v1/admin/account/{accountId}/cluster/{id}`
- `PUT /api/v1/admin/account/{accountId}/cluster/{id}`

**Delete Account Cluster**
- `POST /api/v1/admin/account/{accountId}/cluster/{id}/delete`
- `DELETE /api/v1/admin/account/{accountId}/cluster/{id}/delete`

#### Admin - User Management

**List All Users**
- `GET /api/v1/admin/users`
- `POST /api/v1/admin/users` *(with search/filter)*

**Create User**
- `POST /api/v1/admin/user/new`
- `PUT /api/v1/admin/user/new`

**Get/Update User**
- `GET /api/v1/admin/user/{id}`
- `POST /api/v1/admin/user/{id}`
- `PUT /api/v1/admin/user/{id}`

**Delete User**
- `POST /api/v1/admin/user/{id}/delete`
- `DELETE /api/v1/admin/user/{id}/delete`

#### Admin - Project Management

**List All Projects**
- `GET /api/v1/admin/projects`
- `POST /api/v1/admin/projects` *(with search/filter)*

**List Projects for Account**
- `GET /api/v1/admin/account/{accountId}/projects`
- `POST /api/v1/admin/account/{accountId}/projects` *(with search/filter)*

**Create Project**
- `POST /api/v1/admin/account/{accountId}/project/new`
- `PUT /api/v1/admin/account/{accountId}/project/new`

**Get/Update Project**
- `GET /api/v1/admin/account/{accountId}/project/{id}`
- `POST /api/v1/admin/account/{accountId}/project/{id}`
- `PUT /api/v1/admin/account/{accountId}/project/{id}`

**Manage Project Variables**
- `GET /api/v1/admin/account/{accountId}/project/{id}/variables`
- `POST /api/v1/admin/account/{accountId}/project/{id}/variables`
- `PUT /api/v1/admin/account/{accountId}/project/{id}/variables`

**Refresh Project Credentials**
- `POST /api/v1/admin/account/{accountId}/project/{id}/refresh-credentials`

**Delete Project**
- `POST /api/v1/admin/account/{accountId}/project/{id}/delete`
- `DELETE /api/v1/admin/account/{accountId}/project/{id}/delete`

#### Admin - Job Management

**Create New Job**
- `POST /api/v1/admin/account/{accountId}/project/{projectId}/job/new`
- `PUT /api/v1/admin/account/{accountId}/project/{projectId}/job/new`

**Get Pending Job Status**
- `GET /api/v1/admin/account/{accountId}/project/{projectId}/job/pending/{newJobId}`

**List Jobs for Project**
- `GET /api/v1/admin/account/{accountId}/project/{projectId}/jobs`
- `POST /api/v1/admin/account/{accountId}/project/{projectId}/jobs` *(with search/filter)*

**Get Job Details**
- `GET /api/v1/admin/account/{accountId}/project/{projectId}/job/{id}`

**Restart Job**
- `POST /api/v1/admin/account/{accountId}/project/{projectId}/job/{jobId}/restart`

**Delete Job**
- `POST /api/v1/admin/account/{accountId}/project/{projectId}/job/{id}/delete`
- `DELETE /api/v1/admin/account/{accountId}/project/{projectId}/job/{id}/delete`

## Response Format

All API responses follow a consistent JSON format:

**Success Response:**
```json
{
  "data": {
    // Response data
  }
}
```

**Error Response:**
```json
{
  "error": {
    "message": "Error description",
    "code": "ERROR_CODE"
  }
}
```

## HTTP Status Codes

The API uses standard HTTP status codes:

- `200 OK`: Request succeeded
- `201 Created`: Resource created successfully
- `400 Bad Request`: Invalid request parameters
- `401 Unauthorized`: Authentication required or failed
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation error
- `500 Internal Server Error`: Server error

## Rate Limiting

*(To be documented based on implementation)*

## Best Practices

1. **Store API Tokens Securely**: Treat API tokens like passwords. Store them in secure credential management systems.

2. **Use Environment Variables**: Don't hardcode tokens in scripts. Use environment variables or configuration files.

3. **Token Rotation**: Regularly rotate your API tokens for security.

4. **Minimize Token Scope**: Generate separate API tokens for different applications or use cases.

5. **Handle Token Expiration**: JWT tokens expire based on the configured TTL. Implement token refresh logic in your applications.

6. **Use HTTPS**: Always use HTTPS when communicating with the Space API.

## Complete Example: Creating a Job

```bash
#!/bin/bash

# Configuration
SPACE_HOSTNAME="space.example.com"
SPACE_API_TOKEN_NAME="ci-token"
SPACE_LOGIN="ci@example.com"
SPACE_API_TOKEN_VALUE="your-api-token-value"
SPACE_PROJECT_ID="project-123"

# Step 1: Get JWT Token
SPACE_JWT_TOKEN=$(curl -s -X POST \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d "{\"username\":\"${SPACE_API_TOKEN_NAME}:${SPACE_LOGIN}\",\"token\":\"${SPACE_API_TOKEN_VALUE}\"}" \
  https://${SPACE_HOSTNAME}/api/v1/login | jq -r '.data.token')

if [ -z "$SPACE_JWT_TOKEN" ] || [ "$SPACE_JWT_TOKEN" = "null" ]; then
  echo "Error: Failed to obtain JWT token"
  exit 1
fi

echo "✓ JWT token obtained"

# Step 2: Create a new job
JOB_RESPONSE=$(curl -s -X POST \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer ${SPACE_JWT_TOKEN}" \
  -H 'Content-Type: application/json' \
  -d '{
    "environment": "production",
    "variables": [
      {"name": "VERSION", "value": "1.0.0"}
    ]
  }' \
  https://${SPACE_HOSTNAME}/api/v1/project/${SPACE_PROJECT_ID}/job/new)

JOB_ID=$(echo "$JOB_RESPONSE" | jq -r '.data.id')

if [ -z "$JOB_ID" ] || [ "$JOB_ID" = "null" ]; then
  echo "Error: Failed to create job"
  echo "$JOB_RESPONSE"
  exit 1
fi

echo "✓ Job created with ID: ${JOB_ID}"

# Step 3: Check job status
JOB_STATUS=$(curl -s -X GET \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer ${SPACE_JWT_TOKEN}" \
  https://${SPACE_HOSTNAME}/api/v1/job/${JOB_ID} | jq -r '.data.status')

echo "✓ Job status: ${JOB_STATUS}"
```

## Makefile Example

```makefile
# Get JWT token and create a job
deploy:
	$(eval SPACE_JWT_TOKEN := $(shell curl -s -X POST \
		-H 'Content-Type: application/json' \
		-H 'Accept: application/json' \
		-d '{"username":"${SPACE_API_TOKEN_NAME}:${SPACE_LOGIN}","token":"${SPACE_API_TOKEN_VALUE}"}' \
		https://${SPACE_HOSTNAME}/api/v1/login | jq -r '.data.token'))
	curl -X POST \
		-H 'Accept: application/json' \
		-H "Authorization: Bearer ${SPACE_JWT_TOKEN}" \
		-H 'Content-Type: application/json' \
		-d '{"environment":"production"}' \
		https://${SPACE_HOSTNAME}/api/v1/project/${SPACE_PROJECT_ID}/job/new
```

## Security Considerations

1. **API Token Security**:
   - API tokens provide full access to your account
   - Never commit tokens to version control
   - Use separate tokens for different environments (CI/CD, development, production)

2. **JWT Token Handling**:
   - JWT tokens are short-lived (configurable via `SPACE_JWT_TTL`)
   - Store tokens securely in memory, not on disk
   - Clear tokens after use

3. **Network Security**:
   - Always use HTTPS/TLS for API communication
   - Validate SSL certificates
   - Use secure network connections

4. **Access Control**:
   - API tokens inherit the permissions of the user who created them
   - Use service accounts with minimal required permissions for automation
   - Regularly audit API token usage
