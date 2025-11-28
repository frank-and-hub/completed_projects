interface suburbItemType {
  suburb_name: string;
}

interface metaType {
  total_page: number;
  current_page: number;
  total_item: number;
  per_page: number;
}

interface linkType {
  next: boolean;
  prev: boolean;
}

interface suburbListType {
  status: boolean;
  message: string;
  data: Array<suburbItemType>;
  meta: metaType;
  link: linkType;
}
