import { RegisterForm } from '@features/auth/components/RegisterForm.tsx';

export const RegisterPage = () => {
  return (
    <div>
      <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Create your account
      </h2>
      <p className="mt-2 text-center text-sm text-gray-600">
        Or{' '}
        <a
          href="/login"
          className="font-medium text-blue-600 hover:text-blue-500"
        >
          sign in to your account
        </a>
      </p>
      <div className="mt-8">
        <RegisterForm />
      </div>
    </div>
  );
};