import axios, { AxiosError, InternalAxiosRequestConfig } from 'axios';
import { config } from '@/config';

export const api = axios.create({
  baseURL: config.apiUrl,
  headers: {
    'Content-Type': 'application/json'
  }
});

api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('token');

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    console.log('API Request:', {
      url: config.url,
      method: config.method,
      headers: config.headers
    });

    return config;
  },
  (error: AxiosError) => {
    console.error('API Request Error:', error);
    return Promise.reject(error);
  }
);

api.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const originalRequest = error.config;

    if (error.response?.status === 401 && originalRequest && !originalRequest.headers._retry) {
      if (originalRequest.url?.includes('/auth/login') ||
        originalRequest.url?.includes('/auth/refresh')) {
        return Promise.reject(error);
      }
      originalRequest.headers._retry = true;

      try {
        const response = await api.get<{ result: { access_token: string,token_type: string,expires_in: number } }>('/auth/refresh');
        const newToken = response.data.result.access_token;

        localStorage.setItem('token', newToken);

        if (originalRequest.headers) {
          originalRequest.headers.Authorization = `Bearer ${newToken}`;
        }

        return api(originalRequest);
      } catch (refreshError) {
        localStorage.removeItem('token');
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

export type ApiError = AxiosError<{
  error: string;
  message: string;
  statusCode: number;
}>;