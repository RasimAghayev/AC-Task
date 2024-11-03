import { create } from 'zustand';
import type {
  AuthStore,
  LoginCredentials,
  RegisterCredentials,
  User
} from '@features/auth/types/auth.types.ts';
import { AuthAPI } from '@features/auth/api/auth.api.ts';

export const useAuthStore = create<AuthStore>((set) => ({
  // Initial state
  user: null,
  isAuthenticated: false,
  isLoading: false,
  error: null,

  // State setters
  setUser: (user) => set({
    user,
    isAuthenticated: !!user
  }),

  setError: (error) => set({ error }),

  // Actions
  login: async (credentials: LoginCredentials) => {
    try {
      set({ isLoading: true, error: null });

      const { access_token } = await AuthAPI.login(credentials);
      localStorage.setItem('token', access_token);
      try {
        const user = await AuthAPI.getProfile();

        set({
          user,
          isAuthenticated: true,
          isLoading: false,
          error: null
        });

      }catch (profileError) {
        localStorage.removeItem('token');
        set({
          user: null,
          isAuthenticated: false,
          isLoading: false,
          error: 'Failed to get user profile'
        });
        throw profileError;
      }
    } catch (error: any) {
      localStorage.removeItem('token');
      set({
        user: null,
        isAuthenticated: false,
        isLoading: false,
        error: error.message || 'Login failed'
      });
      throw error;
    }
  },

  register: async (data: RegisterCredentials) => {
    try {
      set({ isLoading: true, error: null });

      const { authorization, user } = await AuthAPI.register(data);
      localStorage.setItem('token', authorization.access_token);

      set({
        user,
        isAuthenticated: true,
        isLoading: false,
        error: null
      });
    } catch (error: any) {
      set({
        error: error.response?.data?.error || 'Registration failed',
        isLoading: false,
        isAuthenticated: false
      });
      throw error;
    }
  },

  logout: async () => {
    try {
      set({ isLoading: true });
      await AuthAPI.logout();
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      localStorage.removeItem('token');
      set({
        user: null,
        isAuthenticated: false,
        isLoading: false,
        error: null
      });
    }
  },

  checkAuth: async () => {
    try {
      const token = localStorage.getItem('token');
      if (!token) {
        return set({ isAuthenticated: false, isLoading: false });
      }

      set({ isLoading: true });
      const user = await AuthAPI.getProfile();

      set({
        user,
        isAuthenticated: true,
        isLoading: false,
        error: null
      });
    } catch (error) {
      localStorage.removeItem('token');
      set({
        user: null,
        isAuthenticated: false,
        isLoading: false,
        error: null
      });
    }
  },

  updateProfile: async (data: Partial<User>) => {
    try {
      set({ isLoading: true, error: null });

      const { user } = await AuthAPI.updateProfile(data);

      set({
        user,
        isLoading: false,
        error: null
      });
    } catch (error: any) {
      set({
        error: error.response?.data?.error || 'Profile update failed',
        isLoading: false
      });
      throw error;
    }
  }
}));