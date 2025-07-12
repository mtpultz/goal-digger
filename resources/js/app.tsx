import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import "./bootstrap";
import App from "./components/App";

// Create root element
const rootElement = document.getElementById("app");

if (!rootElement) {
    throw new Error("Root element 'app' not found");
}

const root = ReactDOM.createRoot(rootElement);

// Render the React app
root.render(
    <React.StrictMode>
        <BrowserRouter>
            <App />
        </BrowserRouter>
    </React.StrictMode>
);
