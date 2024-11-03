import { api } from '@/shared/lib/api';
import type {
  ApiResponse,
  User,
  LoginCredentials,
  RegisterCredentials,
  TokenResponse
} from '@features/auth/types/auth.types.ts';

type LoginResponse = ApiResponse<{
  access_token: string;
  token_type: string;
  expires_in: number;
}>;

type RegisterResponse = ApiResponse<{
  user: User;
  authorization: TokenResponse;
}>;

type ProfileResponse = ApiResponse<User>;

type UpdateProfileResponse = ApiResponse<{
  user: User;
}>;

export const AuthAPI = {
  login: async (credentials: LoginCredentials) => {
    const response = await api.post<LoginResponse>('/auth/login', credentials);
    return response.data.result;
  },

  register: async (data: RegisterCredentials) => {
    const response = await api.post<RegisterResponse>('/auth/register', data);
    return response.data.result;
  },

  getProfile: async () => {
    const response = await api.get<ProfileResponse>('/auth/me');
    return response.data.result;
  },

  updateProfile: async (data: Partial<User>) => {
    const response = await api.put<UpdateProfileResponse>('/auth/me', data);
    return response.data.result;
  },

  logout: async () => {
    await api.post('/auth/logout');
  },

  refreshToken: async () => {
    const response = await api.get<LoginResponse>('/auth/refresh');
    return response.data.result;
  }
};