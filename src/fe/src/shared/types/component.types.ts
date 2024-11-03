import React from 'react';

export type OmitCommonProps<
  T extends React.ElementType,
  OmitAdditionalProps extends keyof any = never
> = Omit<React.ComponentPropsWithoutRef<T>, 'as' | OmitAdditionalProps>;

export type RightJoinProps<
  SourceProps,
  OverrideProps extends object = {}
> = Omit<SourceProps, keyof OverrideProps> & OverrideProps;

export type PropsWithoutRef<T extends React.ElementType> = React.ComponentPropsWithoutRef<T>;

export type PropsWithRef<T extends React.ElementType> = React.ComponentPropsWithRef<T>;

export type MergeWithAs<
  ComponentProps extends object,
  AsProps,
  AdditionalProps extends object = {},
  AsComponent extends React.ElementType = React.ElementType
> = RightJoinProps<
  ComponentProps,
  AdditionalProps & {
  as?: AsComponent;
} & RightJoinProps<AsProps, AdditionalProps>
>;

export type ComponentWithAs<
  Component extends React.ElementType,
  Props extends object = {}
> = {
  <AsComponent extends React.ElementType = Component>(
    props: MergeWithAs<
      React.ComponentPropsWithRef<Component>,
      React.ComponentPropsWithRef<AsComponent>,
      Props,
      AsComponent
    >
  ): React.ReactElement | null;
  displayName?: string;
};

export type PolymorphicComponentProp<
  C extends React.ElementType,
  Props = {}
> = React.PropsWithChildren<Props & { as?: C }> &
  Omit<React.ComponentPropsWithoutRef<C>, keyof Props>;

export type PolymorphicRef<C extends React.ElementType> = React.ComponentPropsWithRef<C>['ref'];

export type PolymorphicComponentPropWithRef<
  C extends React.ElementType,
  Props = {}
> = PolymorphicComponentProp<C, Props> & { ref?: PolymorphicRef<C> };

export interface ComponentProps<T extends React.ElementType = React.ElementType> {
  as?: T;
  children?: React.ReactNode;
}

export type ForwardRefComponent<T, P = {}> = React.ForwardRefExoticComponent<
  React.PropsWithoutRef<P> & React.RefAttributes<T>
>;

export type HTMLProps<T extends React.ElementType> = Omit<
  React.ComponentPropsWithoutRef<T>,
  keyof ComponentProps
>;


/*
interface ButtonProps<C extends React.ElementType = 'button'>
  extends PolymorphicComponentPropWithRef<C> {
  variant?: 'primary' | 'secondary';
  size?: 'sm' | 'md' | 'lg';
}

type ButtonComponent = <C extends React.ElementType = 'button'>(
  props: ButtonProps<C>
) => React.ReactElement | null;

const Button: ButtonComponent = React.forwardRef(
  <C extends React.ElementType = 'button'>(
    { as, variant = 'primary', size = 'md', ...props }: ButtonProps<C>,
    ref?: PolymorphicRef<C>
  ) => {
    const Component = as || 'button';
    return <Component ref={ref} {...props} />;
  }
);
*/