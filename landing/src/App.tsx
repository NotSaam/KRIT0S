import Nav from "./components/Nav";
import Hero from "./components/Hero";
import Marquee from "./components/Marquee";
import Problem from "./components/Problem";
import Workflow from "./components/Workflow";
import CvssCalculator from "./components/CvssCalculator";
import ReportPreview from "./components/ReportPreview";
import AppMock from "./components/AppMock";
import Roles from "./components/Roles";
import Architecture from "./components/Architecture";
import Cta from "./components/Cta";
import Footer from "./components/Footer";

export default function App() {
  return (
    <div className="noise">
      <a href="#main" className="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[70] focus:rounded focus:bg-panel focus:px-3 focus:py-2">
        Saltar al contenido
      </a>
      <Nav />
      <main id="main">
        <Hero />
        <Marquee />
        <Problem />
        <Workflow />
        <CvssCalculator />
        <ReportPreview />
        <AppMock />
        <Roles />
        <Architecture />
        <Cta />
      </main>
      <Footer />
    </div>
  );
}
