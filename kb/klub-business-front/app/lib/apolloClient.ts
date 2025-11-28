import { baseUrl } from '@/utils/axios';
import { ApolloClient, InMemoryCache } from '@apollo/client';

const client = new ApolloClient({
  uri: `${baseUrl}`,
  cache: new InMemoryCache(),
});

export default client;
