# User Module - GraphQL API

This module provides GraphQL mutations and queries for user management.

## Available Operations

### Queries
- `users`: Get all users
- `user(id: String!)`: Get a specific user by ID

### Mutations
- `createUser(input: CreateUserDto!)`: Create a new user
- `updateUser(id: String!, input: UpdateUserDto!)`: Update an existing user
- `deleteUser(id: String!)`: Soft delete a user

## Example GraphQL Queries

### Get All Users
```graphql
query {
  users {
    id
    firstName
    lastName
    email
    status
    createdAt
  }
}
```

### Get Single User
```graphql
query {
  user(id: "user-id-here") {
    id
    firstName
    lastName
    email
    phone
    status
    role {
      id
      name
    }
  }
}
```

### Create User
```graphql
mutation {
  createUser(input: {
    firstName: "John"
    lastName: "Doe"
    email: "john.doe@example.com"
    phone: "+1234567890"
    isNotify: true
    status: ACTIVE
  }) {
    ok
    message
    user {
      id
      firstName
      lastName
      email
    }
  }
}
```

### Update User
```graphql
mutation {
  updateUser(
    id: "user-id-here"
    input: {
      firstName: "Jane"
      lastName: "Smith"
    }
  ) {
    ok
    message
    user {
      id
      firstName
      lastName
      email
    }
  }
}
```

### Delete User
```graphql
mutation {
  deleteUser(id: "user-id-here") {
    ok
    message
  }
}
```

## Security

All mutations are protected with authentication guards. Make sure to implement proper JWT token validation in the `GqlAuthGuard` for production use.

## Response Format

All mutations return a `UserResponse` object with:
- `ok: Boolean` - Whether the operation was successful
- `message: String` - Success or error message
- `user: UserEntity` - The user object (null for delete operations)
