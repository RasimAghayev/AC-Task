import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '@/features/auth/hooks/useAuth';

export const PrivateRoute = () => {
  const { isAuthenticated, isLoading } = useAuth();
  const location = useLocation();

  if (isLoading) {
    return <div>Loading...</div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  return <Outlet />;
};