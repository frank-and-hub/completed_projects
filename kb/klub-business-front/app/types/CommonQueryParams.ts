
export interface CommonQueryParams {
  page?: number;
  limit?: number;
  orderBy?: string;
  direction?: 'asc' | 'desc';
  search?: string;
  [key: string]: any;
}

export interface Option {
  value: string;
  label: string;
}

export interface CommonSelectProps {
  id: string;
  label: string;
  placeholder?: string;
  required?: boolean;
  apiUrl: string;
  labelKey?: string;
  valueKey?: string;
  [x: string]: any;
}

export interface SelectOptionsState {
  options: {
    [key: string]: Option[];
  };
  loading: {
    [key: string]: boolean;
  };
}

export interface SelectOptionsState {
  options: Record<string, Option[]>;
  loading: Record<string, boolean>;
  hasMore: Record<string, boolean>;
  page: Record<string, number>;
}

export type fetchOptions = {
  key: string;
  url: string;
  page: number;
  labelKey: string;
  valueKey: string;
  search?: string;
}

export interface FilterProps {
  filters: Record<string, any>;
  setFilters: React.Dispatch<React.SetStateAction<Record<string, any>>>;
  showFilters: boolean;
  setShowFilters: React.Dispatch<React.SetStateAction<boolean>>;
}

export interface TableViewProps {
  resource: string;
  columns: { key: string; label: string }[];
  addUrl: string;
  editUrl: (id: number | string) => string;
  viewUrl: (id: number | string) => string;
  canDelete: boolean;
  filters: any[];
  [x: string]: any
}

export interface PaginatedResponse<T> {
  data: T[];
  title: string | null;
  pagination: {
    total: number;
    page: number;
    limit: number;
    pages: number;
  }
}

export interface StaticSelectProps {
    name: string;
    label?: string;
    required?: boolean;
    [x: string]: any;
}

