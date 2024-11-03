export const config = {
  apiUrl: import.meta.env.VITE_API_URL || 'http://localhost/api/v1',
  appTitle: import.meta.env.VITE_APP_TITLE || 'Task Management',
  tokenKey: 'token',
  refreshTokenInterval: 50 * 60 * 1000, // 50 minutes
  authPaths: {
    login: '/auth/login',
    register: '/auth/register',
    logout: '/auth/logout',
    refresh: '/auth/refresh',
    me: '/auth/me'
  }
} as const;

// Type for config
export type Config = typeof config;