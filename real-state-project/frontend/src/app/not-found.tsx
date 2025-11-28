// export default function NotFound() {
//   return (
//     <div className="not-found-container">
//       <h1 fontsize="40px"> 404 - Page Not Found</h1>
//       <p>Oops! This page doesn't exist.</p>
//       <a href="/">Go back to home</a>
//     </div>
//   );
// }

import React from "react";
import NoPageFound from "./invitation-accepted/NoPageFound";

function NoFound() {
  return (
    // <Container>
    //   <Lottie
    //     animationData={NoPageFound}
    //     loop={true}
    //     height={"50vh"}
    //     style={{ height: "80vh" }}
    //   />
    //   ;

    <NoPageFound />
    // </Container>
  );
}

export default NoFound;
