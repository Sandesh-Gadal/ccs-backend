import React from "react";
import axios from "axios";

export default function DeleteModal({ category, onClose, onDelete }) {
    const confirmDelete = async () => {
        await axios.delete(`/categories/${category.id}`);
        onDelete();
        onClose();
    };

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
            <div className="bg-white p-6 rounded shadow w-96">
                <h3 className="text-xl font-bold mb-3">Delete Category?</h3>

                <p className="mb-4">
                    Are you sure you want to delete{" "}
                    <span className="font-semibold">{category.name}</span>?
                </p>

                <div className="flex justify-end gap-2">
                    <button
                        className="px-4 py-2 bg-gray-400 text-white rounded"
                        onClick={onClose}
                    >
                        Cancel
                    </button>

                    <button
                        className="px-4 py-2 bg-red-600 text-white rounded"
                        onClick={confirmDelete}
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    );
}
