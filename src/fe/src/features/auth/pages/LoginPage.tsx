import { LoginForm } from '@features/auth/components/LoginForm.tsx';

export const LoginPage = () => {
  return (
    <div>
      <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Sign in to your account
      </h2>
      <p className="mt-2 text-center text-sm text-gray-600">
        Or{' '}
        <a
          href="/register"
          className="font-medium text-blue-600 hover:text-blue-500"
        >
          create a new account
        </a>
      </p>
      <div className="mt-8">
        <LoginForm />
      </div>
    </div>
  );
};