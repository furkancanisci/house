import React, { useState } from 'react';
import { 
  CheckCircle2, 
  XCircle, 
  AlertCircle,
  Info
} from 'lucide-react';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Textarea } from './ui/textarea';

interface FormFieldWithValidationProps {
  label: string;
  name: string;
  value: string | number;
  onChange: (value: any) => void;
  error?: string;
  touched?: boolean;
  required?: boolean;
  type?: 'text' | 'email' | 'password' | 'number' | 'tel' | 'textarea';
  placeholder?: string;
  helpText?: string;
  showSuccess?: boolean;
  maxLength?: number;
  min?: number;
  max?: number;
  disabled?: boolean;
  autoComplete?: string;
}

/**
 * Form Field with Validation following Material Design and iOS patterns
 * Provides clear, immediate feedback on form input
 */
const FormFieldWithValidation: React.FC<FormFieldWithValidationProps> = ({
  label,
  name,
  value,
  onChange,
  error,
  touched = false,
  required = false,
  type = 'text',
  placeholder,
  helpText,
  showSuccess = true,
  maxLength,
  min,
  max,
  disabled = false,
  autoComplete
}) => {
  const [isFocused, setIsFocused] = useState(false);
  const hasError = touched && error;
  const isValid = touched && !error && value && showSuccess;

  // Determine field state for styling
  const getFieldState = () => {
    if (hasError) return 'error';
    if (isValid) return 'success';
    if (isFocused) return 'focused';
    return 'default';
  };

  const fieldState = getFieldState();

  // Get border color based on state
  const getBorderClass = () => {
    switch (fieldState) {
      case 'error':
        return 'border-red-500 focus:border-red-500 focus:ring-red-500/20';
      case 'success':
        return 'border-green-500 focus:border-green-500 focus:ring-green-500/20';
      case 'focused':
        return 'border-[#067977] focus:border-[#067977] focus:ring-[#067977]/20';
      default:
        return 'border-gray-300 focus:border-[#067977] focus:ring-[#067977]/20';
    }
  };

  // Get background color based on state
  const getBackgroundClass = () => {
    switch (fieldState) {
      case 'error':
        return 'bg-red-50';
      case 'success':
        return 'bg-green-50';
      default:
        return 'bg-white';
    }
  };

  // Get label color based on state
  const getLabelClass = () => {
    switch (fieldState) {
      case 'error':
        return 'text-red-700';
      case 'success':
        return 'text-green-700';
      case 'focused':
        return 'text-[#067977]';
      default:
        return 'text-gray-700';
    }
  };

  const fieldClasses = `
    ${getBorderClass()}
    ${getBackgroundClass()}
    transition-all duration-200
    focus:ring-2 focus:ring-offset-0
    disabled:opacity-50 disabled:cursor-not-allowed
  `;

  return (
    <div className="space-y-2">
      {/* Label with required indicator */}
      <Label 
        htmlFor={name}
        className={`flex items-center gap-1 text-sm font-medium ${getLabelClass()} transition-colors duration-200`}
      >
        {label}
        {required && <span className="text-red-500">*</span>}
      </Label>

      {/* Input/Textarea Field */}
      <div className="relative">
        {type === 'textarea' ? (
          <Textarea
            id={name}
            name={name}
            value={value}
            onChange={(e) => onChange(e.target.value)}
            onFocus={() => setIsFocused(true)}
            onBlur={() => setIsFocused(false)}
            placeholder={placeholder}
            disabled={disabled}
            maxLength={maxLength}
            className={fieldClasses}
            aria-invalid={!!hasError}
            aria-describedby={`${name}-error ${name}-help`}
          />
        ) : (
          <Input
            id={name}
            name={name}
            type={type}
            value={value}
            onChange={(e) => {
              const val = type === 'number' ? e.target.valueAsNumber : e.target.value;
              onChange(val);
            }}
            onFocus={() => setIsFocused(true)}
            onBlur={() => setIsFocused(false)}
            placeholder={placeholder}
            disabled={disabled}
            maxLength={maxLength}
            min={min}
            max={max}
            autoComplete={autoComplete}
            className={`${fieldClasses} pr-10`}
            aria-invalid={!!hasError}
            aria-describedby={`${name}-error ${name}-help`}
          />
        )}

        {/* Validation Icons - Following standard patterns */}
        <div className="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
          {hasError && (
            <XCircle className="h-5 w-5 text-red-500 animate-in fade-in duration-200" />
          )}
          {isValid && (
            <CheckCircle2 className="h-5 w-5 text-green-500 animate-in fade-in duration-200" />
          )}
        </div>
      </div>

      {/* Character Counter for textarea/text inputs */}
      {maxLength && (type === 'textarea' || type === 'text') && (
        <div className="flex justify-end">
          <span className={`text-xs ${
            String(value).length > maxLength * 0.9 ? 'text-amber-600' : 'text-gray-500'
          }`}>
            {String(value).length} / {maxLength}
          </span>
        </div>
      )}

      {/* Error Message with animation */}
      {hasError && (
        <div className="flex items-start gap-1.5 animate-in slide-in-from-top-1 duration-200">
          <AlertCircle className="h-4 w-4 text-red-500 mt-0.5 flex-shrink-0" />
          <p id={`${name}-error`} className="text-sm text-red-600">
            {error}
          </p>
        </div>
      )}

      {/* Success Message */}
      {isValid && !hasError && (
        <div className="flex items-start gap-1.5 animate-in slide-in-from-top-1 duration-200">
          <CheckCircle2 className="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
          <p className="text-sm text-green-600">
            Looks good!
          </p>
        </div>
      )}

      {/* Help Text */}
      {helpText && !hasError && !isValid && (
        <div className="flex items-start gap-1.5">
          <Info className="h-4 w-4 text-gray-400 mt-0.5 flex-shrink-0" />
          <p id={`${name}-help`} className="text-sm text-gray-500">
            {helpText}
          </p>
        </div>
      )}
    </div>
  );
};

export default FormFieldWithValidation;