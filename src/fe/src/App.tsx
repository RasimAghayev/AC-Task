import { Routes, Route, Navigate } from 'react-router-dom';
import { Toaster } from '@/shared/components/ui/toaster';

// Layouts
import { AuthLayout } from '@/shared/components/layouts/AuthLayout';
import { DashboardLayout } from '@/shared/components/layouts/DashboardLayout';

// Route Guards
import { PublicRoute } from '@/shared/components/routes/PublicRoute';
import { PrivateRoute } from '@/shared/components/routes/PrivateRoute';

// Pages
import { LoginPage } from '@/features/auth/pages/LoginPage';
import { RegisterPage } from '@/features/auth/pages/RegisterPage';
import { DashboardPage } from '@/features/dashboard/pages/DashboardPage';
import { TasksPage } from '@/features/tasks/pages/TasksPage';
// import { CalendarPage } from '@/features/calendar/pages/CalendarPage';
// import { TeamPage } from '@/features/team/pages/TeamPage';
// import { SettingsPage } from '@/features/settings/pages/SettingsPage';

const App = () => {
  return (
    <>
      <Routes>
        <Route path="/" element={<Navigate to="/dashboard" replace />} />

        {/* Public Routes */}
        <Route element={<PublicRoute />}>
          <Route element={<AuthLayout />}>
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
          </Route>
        </Route>

        {/* Protected Routes */}
        <Route element={<PrivateRoute />}>
          <Route element={<DashboardLayout />}>
            <Route path="/dashboard" element={<DashboardPage />} />
            <Route path="/tasks" element={<TasksPage />} />
            {/*<Route path="/calendar" element={<CalendarPage />} />*/}
            {/*<Route path="/team" element={<TeamPage />} />*/}
            {/*<Route path="/settings" element={<SettingsPage />} />*/}
          </Route>
        </Route>
      </Routes>
      <Toaster />
    </>
  );
};

export default App;