import axios from "axios";
import React, { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";

const GoalDetail = () => {
    const { id } = useParams();
    const [goal, setGoal] = useState(null);
    const [comments, setComments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const [newComment, setNewComment] = useState("");
    const [submittingComment, setSubmittingComment] = useState(false);

    useEffect(() => {
        fetchGoal();
        fetchComments();
    }, [id]);

    const fetchGoal = async () => {
        try {
            const response = await axios.get(`/api/goals/${id}`);
            setGoal(response.data.data);
        } catch (error) {
            console.error("Error fetching goal:", error);
            setError("Failed to load goal");
        } finally {
            setLoading(false);
        }
    };

    const fetchComments = async () => {
        try {
            const response = await axios.get(`/api/goals/${id}/comments`);
            setComments(response.data.data || []);
        } catch (error) {
            console.error("Error fetching comments:", error);
        }
    };

    const handleSubmitComment = async (e) => {
        e.preventDefault();
        if (!newComment.trim()) return;

        setSubmittingComment(true);
        try {
            const response = await axios.post(`/api/goals/${id}/comments`, {
                content: newComment,
            });
            setComments([...comments, response.data.data]);
            setNewComment("");
        } catch (error) {
            console.error("Error submitting comment:", error);
            setError("Failed to submit comment");
        } finally {
            setSubmittingComment(false);
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case "completed":
                return "bg-green-100 text-green-800";
            case "in_progress":
                return "bg-blue-100 text-blue-800";
            case "not_started":
                return "bg-gray-100 text-gray-800";
            default:
                return "bg-gray-100 text-gray-800";
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    if (error || !goal) {
        return (
            <div className="max-w-4xl mx-auto">
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {error || "Goal not found"}
                </div>
                <Link
                    to="/goals"
                    className="inline-block mt-4 text-blue-600 hover:text-blue-700"
                >
                    ← Back to Goals
                </Link>
            </div>
        );
    }

    return (
        <div className="max-w-4xl mx-auto">
            <div className="mb-6">
                <Link
                    to="/goals"
                    className="text-blue-600 hover:text-blue-700 font-medium"
                >
                    ← Back to Goals
                </Link>
            </div>

            <div className="bg-white rounded-lg shadow-lg p-8 mb-8">
                <div className="flex justify-between items-start mb-6">
                    <h1 className="text-3xl font-bold text-gray-900">
                        {goal.title}
                    </h1>
                    <span
                        className={`px-3 py-1 rounded-full text-sm font-medium ${getStatusColor(
                            goal.status
                        )}`}
                    >
                        {goal.status.replace("_", " ")}
                    </span>
                </div>

                <div className="grid md:grid-cols-2 gap-8 mb-6">
                    <div>
                        <h3 className="text-lg font-semibold text-gray-800 mb-2">
                            Description
                        </h3>
                        <p className="text-gray-600 whitespace-pre-wrap">
                            {goal.description}
                        </p>
                    </div>

                    <div className="space-y-4">
                        <div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-2">
                                Details
                            </h3>
                            <div className="space-y-2 text-sm">
                                <div>
                                    <span className="font-medium text-gray-700">
                                        Category:
                                    </span>{" "}
                                    <span className="capitalize">
                                        {goal.category}
                                    </span>
                                </div>
                                {goal.target_date && (
                                    <div>
                                        <span className="font-medium text-gray-700">
                                            Target Date:
                                        </span>{" "}
                                        {new Date(
                                            goal.target_date
                                        ).toLocaleDateString()}
                                    </div>
                                )}
                                <div>
                                    <span className="font-medium text-gray-700">
                                        Created:
                                    </span>{" "}
                                    {new Date(
                                        goal.created_at
                                    ).toLocaleDateString()}
                                </div>
                                {goal.updated_at &&
                                    goal.updated_at !== goal.created_at && (
                                        <div>
                                            <span className="font-medium text-gray-700">
                                                Last Updated:
                                            </span>{" "}
                                            {new Date(
                                                goal.updated_at
                                            ).toLocaleDateString()}
                                        </div>
                                    )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Comments Section */}
            <div className="bg-white rounded-lg shadow-lg p-8">
                <h2 className="text-2xl font-bold text-gray-900 mb-6">
                    Comments
                </h2>

                {/* Add Comment Form */}
                <form onSubmit={handleSubmitComment} className="mb-8">
                    <div className="mb-4">
                        <label
                            htmlFor="comment"
                            className="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Add a comment
                        </label>
                        <textarea
                            id="comment"
                            value={newComment}
                            onChange={(e) => setNewComment(e.target.value)}
                            rows="3"
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Share your thoughts or progress..."
                            required
                        />
                    </div>
                    <button
                        type="submit"
                        disabled={submittingComment || !newComment.trim()}
                        className="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md font-medium"
                    >
                        {submittingComment ? "Posting..." : "Post Comment"}
                    </button>
                </form>

                {/* Comments List */}
                {comments.length === 0 ? (
                    <div className="text-center py-8 text-gray-500">
                        <p>
                            No comments yet. Be the first to share your
                            thoughts!
                        </p>
                    </div>
                ) : (
                    <div className="space-y-6">
                        {comments.map((comment) => (
                            <div
                                key={comment.id}
                                className="border-b border-gray-200 pb-6 last:border-b-0"
                            >
                                <div className="flex justify-between items-start mb-2">
                                    <div className="flex items-center space-x-2">
                                        <span className="font-medium text-gray-900">
                                            {comment.user?.name || "Anonymous"}
                                        </span>
                                        <span className="text-sm text-gray-500">
                                            {new Date(
                                                comment.created_at
                                            ).toLocaleDateString()}
                                        </span>
                                    </div>
                                </div>
                                <p className="text-gray-700 whitespace-pre-wrap">
                                    {comment.content}
                                </p>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
};

export default GoalDetail;
