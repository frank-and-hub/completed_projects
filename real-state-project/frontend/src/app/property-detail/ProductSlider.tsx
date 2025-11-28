import React, { Suspense, useMemo } from "react";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";
import Slider from "react-slick";
import "./propertyDetail.scss";
import { useSearchParams } from "next/navigation";
import { Image } from "@mantine/core";
import { Lightbox } from "yet-another-react-lightbox";
import "yet-another-react-lightbox/styles.css";
interface ProductSliderType {
  images: Array<photosItemType>;
  isDummy?: boolean;
}
function ProductSlider({ images, isDummy }: ProductSliderType) {
  // Settings for the slider
  const [selectedIndex, setSelectedIndex] = React.useState<number>();

  const searchParams = useSearchParams();
  const isFromMap = searchParams?.get("is_from_map");
  const baseUrl = useMemo(() => {
    if (isDummy) {
      return images;
    } else {
      const first = images?.find((item) => item?.isMain);
      const temp = images
        ?.filter((item) => !item?.isMain)
        .map((item) => item?.image);
      const arr = [first?.image, ...temp];
      return arr ?? [];
    }
  }, [images]);

  const settings = {
    customPaging: function (i: number) {
      return (
        <a>
          <Image
            src={baseUrl[i]}
            alt={`Thumbnail ${i + 1}`}
            style={{ width: "100%", height: "100%", borderRadius: 12 }}
          />
        </a>
      );
    },
    dots: true,
    nav: true,
    dotsClass: "slick-dots slick-thumb",
    infinite: true,
    speed: 500,
    slidesToShow: 1,
    slidesToScroll: 1,
    adaptiveHeight: true,
  };

  return (
    <Suspense>
      <div className="slider-container">
        <Lightbox
          index={selectedIndex}
          open={selectedIndex !== undefined}
          close={() => setSelectedIndex(undefined)}
          slides={
            !baseUrl.length
              ? []
              : baseUrl?.map((url, index) => ({
                  src: url as string,
                  alt: `Slide ${index + 1}`,
                }))
          }
        />
        {baseUrl?.length === 1 ? (
          baseUrl.map((url, index) => (
            <div key={index}>
              <Image
                src={url}
                alt={`Slide ${index + 1}`}
                style={{
                  width: "100%",
                  // height: 500,
                  height: isFromMap === "true" ? 500 : "auto",
                }}
              />
            </div>
          ))
        ) : (
          /* @ts-expect-error Server Component */

          <Slider {...settings}>
            {baseUrl.map((url, index) => (
              <div
                key={index}
                onClick={() => setSelectedIndex(index)}
                style={{
                  cursor: "zoom-in",
                }}
              >
                <Image
                  src={url}
                  alt={`Slide ${index + 1}`}
                  style={{
                    width: "100%",
                    height: 500,
                    objectFit: "cover",
                    cursor: "zoom-in",

                    // height: isFromMap === "true" ? 500 : "auto",
                  }}
                />
              </div>
            ))}
          </Slider>
        )}
      </div>
    </Suspense>
  );
}

export default ProductSlider;
