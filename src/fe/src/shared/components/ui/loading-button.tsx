import * as React from 'react';
import { cn } from '@/shared/utils/cn';

interface LoadingButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  isLoading?: boolean;
  loadingText?: string;
  variant?: 'default' | 'destructive' | 'outline' | 'primary';
  size?: 'sm' | 'md' | 'lg';
}

export const LoadingButton = React.forwardRef<HTMLButtonElement, LoadingButtonProps>(
  ({
     className,
     children,
     isLoading,
     loadingText,
     variant = 'primary',
     size = 'md',
     disabled,
     ...props
   }, ref) => {
    const variants = {
      default: 'bg-gray-600 hover:bg-gray-700',
      destructive: 'bg-red-600 hover:bg-red-700',
      outline: 'border-2 border-gray-300 hover:bg-gray-100 text-gray-700',
      primary: 'bg-blue-600 hover:bg-blue-700'
    };

    const sizes = {
      sm: 'h-8 px-3 text-sm',
      md: 'h-10 px-4',
      lg: 'h-12 px-6 text-lg'
    };

    const spinnerSizes = {
      sm: 'h-4 w-4',
      md: 'h-5 w-5',
      lg: 'h-6 w-6'
    };

    return (
      <button
        className={cn(
          'relative inline-flex items-center justify-center font-medium',
          'rounded-md text-white transition-colors duration-200',
          'focus:outline-none focus:ring-2 focus:ring-offset-2',
          'disabled:cursor-not-allowed disabled:opacity-50',
          variants[variant],
          sizes[size],
          className
        )}
        disabled={isLoading || disabled}
        ref={ref}
        {...props}
      >
        {isLoading ? (
          <div className="absolute inset-0 flex items-center justify-center gap-2">
            <div className="relative">
              <div
                className={cn(
                  'animate-[spin_0.6s_linear_infinite]',
                  spinnerSizes[size],
                  'border-2 border-white/20 rounded-full',
                  'border-l-white border-t-white'
                )}
              />
            </div>
            {loadingText && (
              <span className="ml-1 text-white">{loadingText}</span>
            )}
          </div>
        ) : (
          <span className="flex items-center gap-2">{children}</span>
        )}
      </button>
    );
  }
);

LoadingButton.displayName = 'LoadingButton';