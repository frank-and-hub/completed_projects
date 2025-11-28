# GraphQL Configuration for Production

This document outlines the GraphQL setup and configuration for the Klub Business Backend.

## 🚀 Production-Ready Features

### 1. **Environment-Based Configuration**
- **Development**: Playground and introspection enabled
- **Production**: Playground and introspection disabled for security
- **Error Handling**: Sanitized errors in production, detailed errors in development

### 2. **Security Features**
- **Global Authentication Guard**: All GraphQL operations are protected by default
- **Public Decorator**: Use `@Public()` to mark operations that don't require authentication
- **Rate Limiting**: Built-in throttling to prevent abuse
- **CORS Configuration**: Configurable CORS settings

### 3. **Performance Optimizations**
- **Schema Generation**: Auto-generated schema file at `src/schema.gql`
- **Inline Tracing**: Performance monitoring in production
- **Sorted Schema**: Consistent schema ordering

## 📁 File Structure

```
src/
├── app.module.ts                 # Main GraphQL configuration
├── guards/
│   └── graphql-auth.guard.ts     # Global GraphQL authentication guard
├── decorators/
│   └── public.decorator.ts       # Public decorator for unprotected operations
└── users/
    ├── users.resolver.ts         # User GraphQL resolver
    ├── entities/user.entity.ts   # User GraphQL entity
    └── dto/                       # Input/Output DTOs
```

## 🔧 Configuration Details

### GraphQL Module Configuration

```typescript
GraphQLModule.forRoot<ApolloDriverConfig>({
  driver: ApolloDriver,
  autoSchemaFile: join(process.cwd(), 'src/schema.gql'),
  playground: process.env.NODE_ENV !== 'production',
  introspection: process.env.NODE_ENV !== 'production',
  sortSchema: true,
  context: ({ req }) => ({ req }),
  plugins: process.env.NODE_ENV !== 'production' 
    ? [ApolloServerPluginLandingPageLocalDefault(), ApolloServerPluginInlineTrace()] 
    : [ApolloServerPluginInlineTrace()],
  formatError: (error) => {
    // Production-safe error formatting
  },
})
```

### Environment Variables

Create a `.env` file with:

```env
NODE_ENV=production
CORS_ORIGIN=https://yourdomain.com
JWT_SECRET=your-super-secret-jwt-key
```

## 🛡️ Security Implementation

### 1. **Global Authentication**
All GraphQL operations are protected by default. To make an operation public:

```typescript
@Query(() => [UserEntity])
@Public()
async users() {
  // This query is publicly accessible
}
```

### 2. **Protected Mutations**
All mutations require authentication:

```typescript
@Mutation(() => UserResponse)
@UseGuards(GqlAuthGuard)
async createUser(@Args('input') input: CreateUserDto) {
  // This mutation requires authentication
}
```

## 📊 Monitoring & Debugging

### Development Mode
- **Playground**: Available at `/graphql`
- **Introspection**: Enabled for schema exploration
- **Error Details**: Full stack traces and error paths

### Production Mode
- **Playground**: Disabled for security
- **Introspection**: Disabled for security
- **Error Sanitization**: Only safe error messages exposed
- **Performance Tracing**: Inline tracing enabled

## 🔄 API Usage Examples

### Query Users
```graphql
query {
  users {
    id
    firstName
    lastName
    email
    status
  }
}
```

### Create User (Protected)
```graphql
mutation {
  createUser(input: {
    firstName: "John"
    lastName: "Doe"
    email: "john@example.com"
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

## 🚨 Production Checklist

- [ ] Set `NODE_ENV=production`
- [ ] Configure proper `CORS_ORIGIN`
- [ ] Set strong `JWT_SECRET`
- [ ] Implement proper authentication in `GraphQLAuthGuard`
- [ ] Test all mutations with authentication
- [ ] Verify playground is disabled in production
- [ ] Monitor error logs and performance

## 🔧 Customization

### Adding New Resolvers
1. Create resolver class with `@Resolver()` decorator
2. Add queries/mutations with proper decorators
3. Use `@Public()` for unprotected operations
4. Use `@UseGuards()` for additional protection

### Custom Error Handling
Modify the `formatError` function in `app.module.ts` to customize error responses.

### Performance Tuning
- Adjust rate limiting in `ThrottlerModule`
- Configure caching strategies
- Monitor with Apollo Studio (optional)

## 📈 Next Steps

1. **Implement JWT Authentication**: Update `GraphQLAuthGuard` with proper JWT validation
2. **Add Authorization**: Implement role-based access control
3. **Add Caching**: Implement Redis caching for frequently accessed data
4. **Add Monitoring**: Integrate with Apollo Studio for production monitoring
5. **Add Testing**: Create comprehensive GraphQL tests
