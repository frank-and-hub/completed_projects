type paginationDataType<T> = {
  data: Array<T>;

  links:
    | {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
      }
    | {};

  meta: metaType;
};

type metaType = {
  current_page: number;
  from: number;
  last_page: number;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
  path: string;
  per_page: number;
  to: number;
  total: number;
  [prop: string]: any;
};
