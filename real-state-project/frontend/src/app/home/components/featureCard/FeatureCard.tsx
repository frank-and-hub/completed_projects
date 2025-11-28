// import '@mantine/carousel/styles.css';
import { Grid, Image } from '@mantine/core';
import NextImage from 'next/image';

import './featureCard.scss';
interface TFeatureCardType {
  item?: featureListItem;
}
function FeatureCard({ item }: TFeatureCardType) {
  return (
    <div className="feature_info_card">
      <Grid>
        <Grid.Col span={6}>
          <div className="feature_info">
            <h4>{item?.heading}</h4>
            <p> {item?.description}</p>
          </div>
        </Grid.Col>
        <Grid.Col span={6}>
          <div className="feature_info">
            <figure>
              <Image
                component={NextImage}
                src={item?.image}
                alt="My image"
                width={500}
                height={500}
              />
            </figure>
          </div>
        </Grid.Col>
      </Grid>
    </div>
  );
}

export default FeatureCard;
