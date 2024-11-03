export interface User {
  id: number;
  name: string;
  email: string;
  createdAt: string;
  updatedAt: string;
}

export interface ApiResponse<T> {
  result: T;
  timestamp: string;
  path: string;
  method: string;
  error: string | null;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterCredentials {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface TokenResponse {
  access_token: string;
  token_type: string;
  expires_in: number;
}

export interface AuthState {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
}

export interface AuthStore extends AuthState {
  setUser: (user: User | null) => void;
  setError: (error: string | null) => void;
  login: (credentials: LoginCredentials) => Promise<void>;
  register: (data: RegisterCredentials) => Promise<void>;
  logout: () => Promise<void>;
  checkAuth: () => Promise<void>;
  updateProfile: (data: Partial<User>) => Promise<void>;
}