import { useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '@features/auth/store/auth.store.ts';
import { LoginCredentials, RegisterCredentials } from '@features/auth/types/auth.types.ts';

export const useAuth = () => {
  const navigate = useNavigate();
  const {
    user,
    isAuthenticated,
    isLoading,
    error,
    login: loginFn,
    register: registerFn,
    logout: logoutFn
  } = useAuthStore();

  const login = useCallback(async (credentials: LoginCredentials) => {
    try {
      await loginFn(credentials);
      navigate('/dashboard');
    } catch (error) {
      throw error;
    }
  }, [loginFn, navigate]);

  const register = useCallback(async (data: RegisterCredentials) => {
    try {
      await registerFn(data);
      navigate('/dashboard');
    } catch (error) {
      throw error;
    }
  }, [registerFn, navigate]);

  const logout = useCallback(async () => {
    try {
      await logoutFn();
      navigate('/login');
    } catch (error) {
      console.error('Logout error:', error);
    }
  }, [logoutFn, navigate]);

  return {
    user,
    isAuthenticated,
    isLoading,
    error,
    login,
    register,
    logout
  };
};