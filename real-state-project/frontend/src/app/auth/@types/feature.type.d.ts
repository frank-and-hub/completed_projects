interface featureListItem {
  id: string;
  heading: string;
  image: string;
  description: string;
}

interface featureList {
  status: boolean;
  message: string;
  data: Array<featureListItem>;
}
