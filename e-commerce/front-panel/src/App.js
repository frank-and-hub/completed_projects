import './App.css'
import 'react-toastify/dist/ReactToastify.css'
import Routing from './components/Routing'
import Notification from './components/admin/comman/notification/Notification'

function App() {
  return (
    <div className={`App`}>
      <Notification />
      <Routing />
    </div>
  );
}

export default App;
