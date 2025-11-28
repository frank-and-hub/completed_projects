import { useEffect } from "react";
// global.d.ts
interface Window {
  MSStream?: any;
}
const useDisableIosTextFieldZoom = () => {
  useEffect(() => {
    const addMaximumScaleToMetaViewport = () => {
      const el = document.querySelector("meta[name=viewport]");

      if (el !== null) {
        let content: any = el.getAttribute("content");
        let re = /maximum\-scale=[0-9\.]+/g;

        if (re.test(content)) {
          content = content.replace(re, "maximum-scale=1.0");
        } else {
          content = [content, "maximum-scale=1.0"].join(", ");
        }

        el.setAttribute("content", content);
      }
    };

    const checkIsIOS = () =>
      /iPad|iPhone|iPod/.test(navigator.userAgent) && !(window as any).MSStream;

    if (checkIsIOS()) {
      addMaximumScaleToMetaViewport();
    }
  }, []);
};

export default useDisableIosTextFieldZoom;
