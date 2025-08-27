import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { useForm, Controller } from 'react-hook-form';
import '@testing-library/jest-dom';
import EnhancedDocumentTypeSelect from '../EnhancedDocumentTypeSelect';
import { PropertyDocumentType } from '../../services/propertyDocumentTypeService';

// Mock document types
const mockDocumentTypes: PropertyDocumentType[] = [
  {
    id: 1,
    name: 'الطابو العقاري (الطابع العادي)',
    description: 'الطابو العقاري العادي للممتلكات السكنية والتجارية',
    sort_order: 1,
    icon: 'FileText',
    color: '#067977',
  },
  {
    id: 2,
    name: 'الطابو العيني (الطابع المحدث)',
    description: 'الطابو المحدث والمطور للعقارات الجديدة',
    sort_order: 2,
    icon: 'RefreshCw',
    color: '#10b981',
  },
];

// Test form component
const TestForm: React.FC<{
  onSubmit: (data: any) => void;
  defaultValues?: any;
}> = ({ onSubmit, defaultValues = {} }) => {
  const { control, handleSubmit, formState: { errors } } = useForm({
    defaultValues: {
      documentTypeId: '',
      ...defaultValues,
    },
  });

  return (
    <form onSubmit={handleSubmit(onSubmit)} data-testid="test-form">
      <Controller
        name="documentTypeId"
        control={control}
        rules={{ required: 'نوع التابو مطلوب' }}
        render={({ field }) => (
          <EnhancedDocumentTypeSelect
            value={field.value}
            onValueChange={field.onChange}
            placeholder="اختر نوع التابو"
            documentTypes={mockDocumentTypes}
            error={errors.documentTypeId?.message}
          />
        )}
      />
      <button type="submit" data-testid="submit-button">
        Submit
      </button>
      {errors.documentTypeId && (
        <span data-testid="error-message">{errors.documentTypeId.message}</span>
      )}
    </form>
  );
};

describe('EnhancedDocumentTypeSelect Integration Tests', () => {
  describe('Form Integration', () => {
    it('integrates properly with react-hook-form', async () => {
      const mockOnSubmit = jest.fn();
      render(<TestForm onSubmit={mockOnSubmit} />);

      // Select a document type
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        fireEvent.click(screen.getByText('الطابو العقاري (الطابع العادي)'));
      });

      // Submit form
      fireEvent.click(screen.getByTestId('submit-button'));

      await waitFor(() => {
        expect(mockOnSubmit).toHaveBeenCalledWith({
          documentTypeId: '1',
        });
      });
    });

    it('shows validation errors when required field is empty', async () => {
      const mockOnSubmit = jest.fn();
      render(<TestForm onSubmit={mockOnSubmit} />);

      // Submit form without selecting anything
      fireEvent.click(screen.getByTestId('submit-button'));

      await waitFor(() => {
        expect(screen.getByTestId('error-message')).toHaveTextContent('نوع التابو مطلوب');
      });

      expect(mockOnSubmit).not.toHaveBeenCalled();
    });

    it('pre-populates with default values', () => {
      const mockOnSubmit = jest.fn();
      render(
        <TestForm 
          onSubmit={mockOnSubmit} 
          defaultValues={{ documentTypeId: '2' }} 
        />
      );

      // Should show the selected value
      expect(screen.getByDisplayValue('الطابو العيني (الطابع المحدث)')).toBeInTheDocument();
    });

    it('updates form state when selection changes', async () => {
      const mockOnSubmit = jest.fn();
      render(
        <TestForm 
          onSubmit={mockOnSubmit} 
          defaultValues={{ documentTypeId: '1' }} 
        />
      );

      // Change selection
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        fireEvent.click(screen.getByText('الطابو العيني (الطابع المحدث)'));
      });

      // Submit form
      fireEvent.click(screen.getByTestId('submit-button'));

      await waitFor(() => {
        expect(mockOnSubmit).toHaveBeenCalledWith({
          documentTypeId: '2',
        });
      });
    });

    it('clears validation errors when valid selection is made', async () => {
      const mockOnSubmit = jest.fn();
      render(<TestForm onSubmit={mockOnSubmit} />);

      // Submit form without selection to trigger validation error
      fireEvent.click(screen.getByTestId('submit-button'));

      await waitFor(() => {
        expect(screen.getByTestId('error-message')).toBeInTheDocument();
      });

      // Make a selection
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        fireEvent.click(screen.getByText('الطابو العقاري (الطابع العادي)'));
      });

      // Error should be cleared
      await waitFor(() => {
        expect(screen.queryByTestId('error-message')).not.toBeInTheDocument();
      });
    });
  });

  describe('API Integration Simulation', () => {
    it('handles loading state during API call', () => {
      const mockOnSubmit = jest.fn();
      
      const LoadingForm: React.FC = () => {
        const { control } = useForm();
        
        return (
          <Controller
            name="documentTypeId"
            control={control}
            render={({ field }) => (
              <EnhancedDocumentTypeSelect
                value={field.value}
                onValueChange={field.onChange}
                placeholder="اختر نوع التابو"
                documentTypes={[]}
                loading={true}
              />
            )}
          />
        );
      };

      render(<LoadingForm />);

      // Click to open dropdown
      fireEvent.click(screen.getByRole('combobox'));

      expect(screen.getByText('جاري تحميل أنواع التابو...')).toBeInTheDocument();
      expect(screen.getByTestId('loading-spinner')).toBeInTheDocument();
    });

    it('handles API error state with fallback', async () => {
      const mockOnSubmit = jest.fn();
      
      const ErrorForm: React.FC = () => {
        const { control } = useForm();
        
        return (
          <Controller
            name="documentTypeId"
            control={control}
            render={({ field }) => (
              <EnhancedDocumentTypeSelect
                value={field.value}
                onValueChange={field.onChange}
                placeholder="اختر نوع التابو"
                documentTypes={mockDocumentTypes} // Fallback data
                error="فشل في تحميل البيانات من الخادم"
              />
            )}
          />
        );
      };

      render(<ErrorForm />);

      // Click to open dropdown
      fireEvent.click(screen.getByRole('combobox'));

      expect(screen.getByText('فشل في تحميل البيانات من الخادم')).toBeInTheDocument();
      expect(screen.getByText('إعادة المحاولة')).toBeInTheDocument();
    });

    it('handles empty API response', async () => {
      const mockOnSubmit = jest.fn();
      
      const EmptyForm: React.FC = () => {
        const { control } = useForm();
        
        return (
          <Controller
            name="documentTypeId"
            control={control}
            render={({ field }) => (
              <EnhancedDocumentTypeSelect
                value={field.value}
                onValueChange={field.onChange}
                placeholder="اختر نوع التابو"
                documentTypes={[]}
                loading={false}
                error={null}
              />
            )}
          />
        );
      };

      render(<EmptyForm />);

      // Click to open dropdown
      fireEvent.click(screen.getByRole('combobox'));

      await waitFor(() => {
        expect(screen.getByText('لا توجد أنواع تابو متاحة')).toBeInTheDocument();
      });
    });
  });

  describe('Performance with Large Datasets', () => {
    it('handles large number of document types efficiently', async () => {
      const largeDataset = Array.from({ length: 1000 }, (_, i) => ({
        id: i + 1,
        name: `نوع التابو ${i + 1}`,
        description: `وصف نوع التابو رقم ${i + 1}`,
        sort_order: i + 1,
        icon: 'File',
        color: '#067977',
      }));

      const mockOnSubmit = jest.fn();
      
      const LargeDataForm: React.FC = () => {
        const { control } = useForm();
        
        return (
          <Controller
            name="documentTypeId"
            control={control}
            render={({ field }) => (
              <EnhancedDocumentTypeSelect
                value={field.value}
                onValueChange={field.onChange}
                placeholder="اختر نوع التابو"
                documentTypes={largeDataset}
              />
            )}
          />
        );
      };

      const startTime = performance.now();
      render(<LargeDataForm />);
      const renderTime = performance.now() - startTime;

      // Should render within reasonable time (less than 100ms)
      expect(renderTime).toBeLessThan(100);

      // Click to open dropdown
      const openStartTime = performance.now();
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        expect(screen.getAllByRole('option')).toHaveLength(1000);
      });
      
      const openTime = performance.now() - openStartTime;
      
      // Should open within reasonable time (less than 200ms)
      expect(openTime).toBeLessThan(200);
    });
  });

  describe('Accessibility Integration', () => {
    it('maintains focus management in form context', async () => {
      const mockOnSubmit = jest.fn();
      render(<TestForm onSubmit={mockOnSubmit} />);

      const trigger = screen.getByRole('combobox');
      
      // Focus the trigger
      trigger.focus();
      expect(trigger).toHaveFocus();

      // Open dropdown with keyboard
      fireEvent.keyDown(trigger, { key: 'Enter' });

      await waitFor(() => {
        expect(screen.getByRole('listbox')).toBeInTheDocument();
      });

      // Navigate to first option
      fireEvent.keyDown(screen.getByRole('listbox'), { key: 'ArrowDown' });

      // Select with Enter
      fireEvent.keyDown(screen.getByRole('listbox'), { key: 'Enter' });

      await waitFor(() => {
        // Focus should return to trigger
        expect(trigger).toHaveFocus();
      });
    });

    it('announces selection changes to screen readers', async () => {
      const mockOnSubmit = jest.fn();
      render(<TestForm onSubmit={mockOnSubmit} />);

      // Open dropdown
      fireEvent.click(screen.getByRole('combobox'));

      await waitFor(() => {
        const firstOption = screen.getAllByRole('option')[0];
        expect(firstOption).toHaveAttribute('aria-selected', 'false');
      });

      // Select first option
      fireEvent.click(screen.getByText('الطابو العقاري (الطابع العادي)'));

      // Reopen to check selection state
      fireEvent.click(screen.getByRole('combobox'));

      await waitFor(() => {
        const selectedOption = screen.getByRole('option', { selected: true });
        expect(selectedOption).toHaveAttribute('aria-selected', 'true');
      });
    });
  });
});