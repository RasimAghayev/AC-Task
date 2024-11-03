import { useState } from 'react';
import { Link, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '@/features/auth/hooks/useAuth';
import {
  LayoutDashboard,
  CheckSquare,
  // Calendar,
  // Settings,
  // Users,
  LogOut,
  Menu,
  X
} from 'lucide-react';

const menuItems = [
  {
    title: 'Dashboard',
    path: '/dashboard',
    icon: LayoutDashboard
  },
  {
    title: 'Tasks',
    path: '/tasks',
    icon: CheckSquare
  },
  // {
  //   title: 'Calendar',
  //   path: '/calendar',
  //   icon: Calendar
  // },
  // {
  //   title: 'Team',
  //   path: '/team',
  //   icon: Users
  // },
  // {
  //   title: 'Settings',
  //   path: '/settings',
  //   icon: Settings
  // }
];

export const DashboardLayout = () => {
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);
  const { user, logout } = useAuth();
  const location = useLocation();

  return (
    <div className="min-h-screen 50">
      {/* Sidebar */}
      <aside
        className={`fixed top-0 left-0 z-40 h-screen bg-white border-r border-gray-200 transition-all duration-300 ${
          isSidebarOpen ? 'w-64' : 'w-16'
        }`}
      >
        {/* Logo/Menu Toggle */}
        <div className="h-16 flex items-center justify-between px-4 border-b border-gray-200">
          {isSidebarOpen && (
            <span className="text-xl font-bold text-gray-800">TaskManager</span>
          )}
          <button
            onClick={() => setIsSidebarOpen(!isSidebarOpen)}
            className="p-2 rounded-lg hover:bg-gray-100"
          >
            {isSidebarOpen ? <X size={20} /> : <Menu size={20} />}
          </button>
        </div>

        {/* Navigation */}
        <nav className="mt-5">
          {menuItems.map((item) => {
            const Icon = item.icon;
            const isActive = location.pathname === item.path;

            return (
              <Link
                key={item.path}
                to={item.path}
                className={`
                  flex items-center px-4 py-3 mx-2 rounded-lg
                  ${isActive ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50'}
                `}
              >
                <Icon size={20} />
                {isSidebarOpen && (
                  <span className="ml-3">{item.title}</span>
                )}
              </Link>
            );
          })}
        </nav>

        {/* User Profile */}
        <div className="absolute bottom-0 left-0 right-0 border-t border-gray-200 p-4">
          <div className="flex items-center">
            <div className="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium">
              {user?.name?.[0]?.toUpperCase()}
            </div>
            {isSidebarOpen && (
              <div className="ml-3">
                <p className="text-sm font-medium text-gray-700">{user?.name}</p>
                <p className="text-xs text-gray-500">{user?.email}</p>
              </div>
            )}
          </div>
          <button
            onClick={logout}
            className="mt-4 flex items-center text-red-600 hover:bg-red-50 px-3 py-2 rounded-lg w-full"
          >
            <LogOut size={20} />
            {isSidebarOpen && <span className="ml-3">Logout</span>}
          </button>
        </div>
      </aside>

      {/* Main Content */}
      <main className={`
        transition-all duration-300
        ${isSidebarOpen ? 'ml-64' : 'ml-20'}
      `}>
        <div className="min-h-screen bg-gray-100">
          {/* Header */}
          <header className="bg-white h-16 fixed right-0 top-0 left-0 shadow-sm z-40 flex items-center px-6 ml-64">
            <h1 className="text-xl font-semibold text-gray-800">
              {menuItems.find(item => item.path === location.pathname)?.title || 'Dashboard'}
            </h1>
          </header>

          {/* Page Content */}
          <div className="pt-16 p-6">
            <Outlet />
          </div>
        </div>
      </main>
    </div>
  );
};