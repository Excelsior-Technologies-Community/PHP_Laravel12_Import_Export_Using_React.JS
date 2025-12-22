import React, { useState } from "react";
import { createRoot } from "react-dom/client";

/**
 * ProductForm Component
 * Handles both CREATE and EDIT operations
 * Used for adding a new product or editing an existing one
 */
function ProductForm({ product, onBack }) {
  // Form state initialization (prefilled in edit mode)
  const [name, setName] = useState(product?.name ?? "");
  const [description, setDescription] = useState(product?.description ?? "");
  const [price, setPrice] = useState(product?.price ?? "");
  const [status, setStatus] = useState(product?.status ?? "active");

  return (
    <div>
      {/* Back button to return to product list */}
      <button className="btn btn-secondary mb-3" onClick={onBack}>
        ← Back to List
      </button>

      {/* Product Create / Update Form */}
      <form
        method="POST"
        action={product ? `/products/${product.id}/update` : "/products/store"}
      >
        {/* CSRF token for Laravel security */}
        <input
          type="hidden"
          name="_token"
          value={document.querySelector('meta[name="csrf-token"]').content}
        />

        {/* Form heading based on mode */}
        <h3>{product ? "Edit Product" : "Add Product"}</h3>

        {/* Product Name Field */}
        <label className="form-label">Name</label>
        <input
          type="text"
          name="name"
          value={name}
          onChange={(e) => setName(e.target.value)}
          className="form-control mb-2"
          required
        />

        {/* Product Description Field */}
        <label className="form-label">Description</label>
        <textarea
          name="description"
          value={description}
          onChange={(e) => setDescription(e.target.value)}
          className="form-control mb-2"
        ></textarea>

        {/* Product Price Field */}
        <label className="form-label">Price</label>
        <input
          type="number"
          step="0.01"
          name="price"
          value={price}
          onChange={(e) => setPrice(e.target.value)}
          className="form-control mb-2"
          required
        />

        {/* Product Status Dropdown */}
        <label className="form-label">Status</label>
        <select
          name="status"
          value={status}
          onChange={(e) => setStatus(e.target.value)}
          className="form-control mb-3"
        >
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>

        {/* Submit Button */}
        <button className="btn btn-primary">
          {product ? "Update" : "Save"}
        </button>
      </form>
    </div>
  );
}

/**
 * ProductIndex Component
 * Displays product list and manages Add, Edit, and Delete actions
 */
function ProductIndex({ products }) {
  // Product list state (excluding deleted products)
  const [list, setList] = useState(
    products.filter((p) => p.status !== "deleted")
  );

  // State to track editing and adding modes
  const [editingProduct, setEditingProduct] = useState(null);
  const [addingProduct, setAddingProduct] = useState(false);

  /**
   * Delete product handler
   * Performs soft delete by setting status to 'deleted'
   */
  const handleDelete = (id) => {
    if (!confirm("Are you sure to delete?")) return;

    // Optimistic UI update (remove row immediately)
    setList(list.filter((p) => p.id !== id));

    // Send delete request to Laravel
    fetch(`/products/${id}/delete`, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
      },
    }).then(() => console.log("Deleted"));
  };

  // Show Create Product form
  if (addingProduct) {
    return (
      <ProductForm
        product={null}
        onBack={() => setAddingProduct(false)}
      />
    );
  }

  // Show Edit Product form
  if (editingProduct) {
    return (
      <ProductForm
        product={editingProduct}
        onBack={() => setEditingProduct(null)}
      />
    );
  }

  // Show Products List Table
  return (
    <div>
      <h2>Products List</h2>

      {/* Add Product Button */}
      <button
        className="btn btn-primary mb-3"
        onClick={() => setAddingProduct(true)}
      >
        + Add Product
      </button>

      {/* Products Table */}
      <table className="table table-bordered">
        <thead>
          <tr>
            <th>Id</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          {list.map((product) => (
            <tr key={product.id}>
              <td>{product.id}</td>
              <td>{product.name}</td>
              <td>{product.description}</td>
              <td>{product.price}</td>
              <td>{product.status}</td>
              <td>
                {/* Edit Button */}
                <button
                  className="btn btn-sm btn-warning me-2"
                  onClick={() => setEditingProduct(product)}
                >
                  Edit
                </button>

                {/* Delete Button */}
                <button
                  className="btn btn-sm btn-danger"
                  onClick={() => handleDelete(product.id)}
                >
                  Delete
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

/**
 * Render React application
 * Mounts ProductIndex component inside Blade view
 */
createRoot(document.getElementById("app")).render(
  <ProductIndex products={window.productsData} />
);
