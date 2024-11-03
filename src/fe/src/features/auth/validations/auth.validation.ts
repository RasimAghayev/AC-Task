import * as z from 'zod';

export const loginSchema = z.object({
  email: z
    .string()
    .min(3, { message: 'Email is required' })
    .email({ message: 'Invalid email address' }),
  password: z
    .string()
    .min(8, { message: 'Password is required' })
    .min(8, { message: 'Password must be at least 8 characters' })
});

export const registerSchema = z.object({
  name: z
    .string()
    .min(1, { message: 'Name is required' })
    .min(2, { message: 'Name must be at least 2 characters' })
    .max(50, { message: 'Name must be at least 50 characters' }),
  email: z
    .string()
    .min(1, { message: 'Email is required' })
    .email({ message: 'Invalid email address' }),
  password: z
    .string()
    .min(1, { message: 'Password is required' })
    .min(8, { message: 'Password must be at least 8 characters' }),
  password_confirmation: z
    .string()
    .min(1, { message: 'Password confirmation is required' })
}).refine((data) => data.password === data.password_confirmation, {
  message: "Passwords don't match",
  path: ["password_confirmation"],
});

export type LoginFormData = z.infer<typeof loginSchema>;
export type RegisterFormData = z.infer<typeof registerSchema>;