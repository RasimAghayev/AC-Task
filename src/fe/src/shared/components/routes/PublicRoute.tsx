import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '@/features/auth/hooks/useAuth';

export const PublicRoute = () => {
  const { isAuthenticated, isLoading } = useAuth();
  const location = useLocation();

  if (isLoading) {
    return <div>Loading...</div>;
  }

  if (isAuthenticated) {
    const from = (location.state as any)?.from?.pathname || '/dashboard';
    return <Navigate to={from} replace />;
  }

  return <Outlet />;
};