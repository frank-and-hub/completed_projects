export const jwtConstants = {
  secret: process.env.JWT_SECRET ?? 'a-string-secret-at-least-256-bits-long',
  refresh_secret: process.env.JWT_REFRESH_SECRET ?? 'a-string-refresh-secret-at-least-256-bits-long',
  expires: (process.env.JWT_EXPIRES_IN ?? '1440m') ?? undefined,
};

export const app = {
  limit:process.env.DATA_PAGINATION_LIMIT ?? '10',
  page:process.env.DATA_DEFAULT_PAGE ?? '1',
}