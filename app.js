import React from 'react';
import Register from './components/Register';
import ProductList from './components/ProductList';

function App() {
  return (
    <div className="container">
      <h1 className="text-center my-4">Online Grocery Store</h1>
      <Register />
      <ProductList />
    </div>
  );
}
export default App;
