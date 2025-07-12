import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import "./bootstrap";
import App from "./components/App";

// Create root element
const root = ReactDOM.createRoot(document.getElementById("app"));

// Render the React app
root.render(
    <React.StrictMode>
        <BrowserRouter>
            <App />
        </BrowserRouter>
    </React.StrictMode>
);
