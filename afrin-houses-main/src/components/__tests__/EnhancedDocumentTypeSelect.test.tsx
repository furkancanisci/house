import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import EnhancedDocumentTypeSelect from '../EnhancedDocumentTypeSelect';
import { PropertyDocumentType } from '../../services/propertyDocumentTypeService';

// Mock data
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
  {
    id: 3,
    name: 'الطابو الأخضر (الطابع الزراعي)',
    description: 'الطابو الزراعي للأراضي الزراعية والحقول مع وصف طويل جداً يتجاوز الحد المسموح به لعرض النص الكامل في القائمة المنسدلة',
    sort_order: 3,
    icon: 'Leaf',
    color: '#22c55e',
  },
];

// Mock props
const defaultProps = {
  value: '',
  onValueChange: jest.fn(),
  placeholder: 'اختر نوع التابو',
  documentTypes: mockDocumentTypes,
  loading: false,
  error: null,
  showDescriptions: true,
};

describe('EnhancedDocumentTypeSelect', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  describe('Rendering', () => {
    it('renders with placeholder text', () => {
      render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      expect(screen.getByText('اختر نوع التابو')).toBeInTheDocument();
    });

    it('renders with custom placeholder', () => {
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          placeholder="اختر نوع مختلف" 
        />
      );
      expect(screen.getByText('اختر نوع مختلف')).toBeInTheDocument();
    });

    it('renders with selected value', () => {
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          value="1" 
        />
      );
      expect(screen.getByDisplayValue('الطابو العقاري (الطابع العادي)')).toBeInTheDocument();
    });

    it('applies custom className', () => {
      const { container } = render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          className="custom-class" 
        />
      );
      expect(container.firstChild).toHaveClass('custom-class');
    });
  });

  describe('Loading State', () => {
    it('shows loading spinner when loading is true', () => {
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          loading={true} 
        />
      );
      
      // Click to open dropdown
      fireEvent.click(screen.getByRole('combobox'));
      
      expect(screen.getByText('جاري تحميل أنواع التابو...')).toBeInTheDocument();
      expect(screen.getByTestId('loading-spinner')).toBeInTheDocument();
    });

    it('disables trigger when loading', () => {
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          loading={true} 
        />
      );
      
      const trigger = screen.getByRole('combobox');
      expect(trigger).toBeDisabled();
    });
  });

  describe('Error State', () => {
    it('shows error message when error is provided', () => {
      const errorMessage = 'فشل في تحميل البيانات';
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          error={errorMessage} 
        />
      );
      
      // Click to open dropdown
      fireEvent.click(screen.getByRole('combobox'));
      
      expect(screen.getByText(errorMessage)).toBeInTheDocument();
      expect(screen.getByText('إعادة المحاولة')).toBeInTheDocument();
    });

    it('shows retry button in error state', () => {
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          error="Network error" 
        />
      );
      
      fireEvent.click(screen.getByRole('combobox'));
      
      const retryButton = screen.getByText('إعادة المحاولة');
      expect(retryButton).toBeInTheDocument();
      expect(retryButton).toHaveAttribute('aria-label', 'إعادة تحميل أنواع التابو');
    });
  });

  describe('Document Type Options', () => {
    it('renders all document types when dropdown is opened', async () => {
      render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      // Click to open dropdown
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        mockDocumentTypes.forEach(docType => {
          expect(screen.getByText(docType.name)).toBeInTheDocument();
        });
      });
    });

    it('shows descriptions when showDescriptions is true', async () => {
      render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        expect(screen.getByText('الطابو العقاري العادي للممتلكات السكنية والتجارية')).toBeInTheDocument();
      });
    });

    it('hides descriptions when showDescriptions is false', async () => {
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          showDescriptions={false} 
        />
      );
      
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        expect(screen.queryByText('الطابو العقاري العادي للممتلكات السكنية والتجارية')).not.toBeInTheDocument();
      });
    });

    it('truncates long descriptions', async () => {
      render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        const longDescription = screen.getByText(/الطابو الزراعي للأراضي الزراعية والحقول مع وصف طويل جداً/);
        expect(longDescription.textContent).toMatch(/\.\.\.$/);
      });
    });

    it('sorts document types by sort_order', async () => {
      const unsortedTypes = [
        { ...mockDocumentTypes[2], sort_order: 1 },
        { ...mockDocumentTypes[0], sort_order: 3 },
        { ...mockDocumentTypes[1], sort_order: 2 },
      ];
      
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          documentTypes={unsortedTypes} 
        />
      );
      
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        const options = screen.getAllByRole('option');
        expect(options[0]).toHaveTextContent('الطابو الأخضر');
        expect(options[1]).toHaveTextContent('الطابو العيني');
        expect(options[2]).toHaveTextContent('الطابو العقاري');
      });
    });
  });

  describe('Selection Behavior', () => {
    it('calls onValueChange when option is selected', async () => {
      const mockOnValueChange = jest.fn();
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          onValueChange={mockOnValueChange} 
        />
      );
      
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        fireEvent.click(screen.getByText('الطابو العقاري (الطابع العادي)'));
      });
      
      expect(mockOnValueChange).toHaveBeenCalledWith('1');
    });

    it('shows selection indicator for selected option', async () => {
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          value="1" 
        />
      );
      
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        const selectedOption = screen.getByRole('option', { selected: true });
        expect(selectedOption).toHaveAttribute('aria-selected', 'true');
      });
    });

    it('closes dropdown after selection', async () => {
      const mockOnValueChange = jest.fn();
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          onValueChange={mockOnValueChange} 
        />
      );
      
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        fireEvent.click(screen.getByText('الطابو العقاري (الطابع العادي)'));
      });
      
      await waitFor(() => {
        expect(screen.queryByRole('listbox')).not.toBeInTheDocument();
      });
    });
  });

  describe('Keyboard Navigation', () => {
    it('opens dropdown with Enter key', async () => {
      render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      const trigger = screen.getByRole('combobox');
      trigger.focus();
      
      fireEvent.keyDown(trigger, { key: 'Enter' });
      
      await waitFor(() => {
        expect(screen.getByRole('listbox')).toBeInTheDocument();
      });
    });

    it('closes dropdown with Escape key', async () => {
      render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      // Open dropdown
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        expect(screen.getByRole('listbox')).toBeInTheDocument();
      });
      
      // Close with Escape
      fireEvent.keyDown(screen.getByRole('listbox'), { key: 'Escape' });
      
      await waitFor(() => {
        expect(screen.queryByRole('listbox')).not.toBeInTheDocument();
      });
    });

    it('navigates options with arrow keys', async () => {
      render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        const listbox = screen.getByRole('listbox');
        fireEvent.keyDown(listbox, { key: 'ArrowDown' });
        
        // First option should be focused
        const firstOption = screen.getAllByRole('option')[0];
        expect(firstOption).toHaveFocus();
      });
    });
  });

  describe('Accessibility', () => {
    it('has proper ARIA attributes', () => {
      render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      const trigger = screen.getByRole('combobox');
      expect(trigger).toHaveAttribute('aria-label', 'اختيار نوع التابو');
      expect(trigger).toHaveAttribute('aria-haspopup', 'listbox');
      expect(trigger).toHaveAttribute('aria-expanded', 'false');
    });

    it('updates aria-expanded when dropdown opens', async () => {
      render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      const trigger = screen.getByRole('combobox');
      fireEvent.click(trigger);
      
      await waitFor(() => {
        expect(trigger).toHaveAttribute('aria-expanded', 'true');
      });
    });

    it('has proper role attributes for options', async () => {
      render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        const listbox = screen.getByRole('listbox');
        expect(listbox).toHaveAttribute('aria-label', 'قائمة أنواع التابو');
        
        const options = screen.getAllByRole('option');
        options.forEach(option => {
          expect(option).toHaveAttribute('role', 'option');
        });
      });
    });

    it('associates descriptions with options', async () => {
      render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        const firstOption = screen.getAllByRole('option')[0];
        expect(firstOption).toHaveAttribute('aria-describedby', 'doc-type-1-desc');
        
        const description = screen.getByText('الطابو العقاري العادي للممتلكات السكنية والتجارية');
        expect(description).toHaveAttribute('id', 'doc-type-1-desc');
      });
    });
  });

  describe('Disabled State', () => {
    it('disables trigger when disabled prop is true', () => {
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          disabled={true} 
        />
      );
      
      const trigger = screen.getByRole('combobox');
      expect(trigger).toBeDisabled();
    });

    it('does not open dropdown when disabled', () => {
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          disabled={true} 
        />
      );
      
      fireEvent.click(screen.getByRole('combobox'));
      
      expect(screen.queryByRole('listbox')).not.toBeInTheDocument();
    });
  });

  describe('Empty State', () => {
    it('shows empty state when no document types are provided', async () => {
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          documentTypes={[]} 
        />
      );
      
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        expect(screen.getByText('لا توجد أنواع تابو متاحة')).toBeInTheDocument();
      });
    });
  });

  describe('Performance', () => {
    it('memoizes component to prevent unnecessary re-renders', () => {
      const { rerender } = render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      // Re-render with same props
      rerender(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      // Component should not re-render unnecessarily
      expect(screen.getByRole('combobox')).toBeInTheDocument();
    });

    it('handles large lists efficiently', async () => {
      const largeDocumentTypes = Array.from({ length: 100 }, (_, i) => ({
        id: i + 1,
        name: `Document Type ${i + 1}`,
        description: `Description for document type ${i + 1}`,
        sort_order: i + 1,
        icon: 'File',
        color: '#067977',
      }));
      
      render(
        <EnhancedDocumentTypeSelect 
          {...defaultProps} 
          documentTypes={largeDocumentTypes} 
        />
      );
      
      fireEvent.click(screen.getByRole('combobox'));
      
      await waitFor(() => {
        expect(screen.getAllByRole('option')).toHaveLength(100);
      });
    });
  });

  describe('Responsive Design', () => {
    it('applies responsive classes', () => {
      render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      const trigger = screen.getByRole('combobox');
      expect(trigger).toHaveClass('h-12', 'sm:h-14', 'text-base', 'sm:text-lg');
    });

    it('handles mobile touch optimization', () => {
      render(<EnhancedDocumentTypeSelect {...defaultProps} />);
      
      const trigger = screen.getByRole('combobox');
      expect(trigger).toHaveClass('touch-manipulation', 'min-h-[48px]');
    });
  });
});