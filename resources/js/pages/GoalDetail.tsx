import axios from "axios";
import React, { ChangeEvent, FormEvent, useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { Alert, AlertDescription, AlertTitle } from "../components/ui/alert";
import { Button } from "../components/ui/button";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "../components/ui/card";
import { Label } from "../components/ui/label";
import { Textarea } from "../components/ui/textarea";

interface Goal {
    id: number;
    title: string;
    description: string;
    status: string;
    category: string;
    target_date?: string;
    created_at: string;
    updated_at: string;
}

interface Comment {
    id: number;
    content: string;
    user?: {
        id: number;
        name: string;
    };
    created_at: string;
    updated_at: string;
}

const GoalDetail: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const [goal, setGoal] = useState<Goal | null>(null);
    const [comments, setComments] = useState<Comment[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [error, setError] = useState<string>("");
    const [newComment, setNewComment] = useState<string>("");
    const [submittingComment, setSubmittingComment] = useState<boolean>(false);

    useEffect(() => {
        if (id) {
            fetchGoal();
            fetchComments();
        }
    }, [id]);

    const fetchGoal = async (): Promise<void> => {
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

    const fetchComments = async (): Promise<void> => {
        try {
            const response = await axios.get(`/api/goals/${id}/comments`);
            setComments(response.data.data || []);
        } catch (error) {
            console.error("Error fetching comments:", error);
        }
    };

    const handleSubmitComment = async (
        e: FormEvent<HTMLFormElement>
    ): Promise<void> => {
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

    const getStatusColor = (status: string): string => {
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
                <Alert variant="destructive">
                    <AlertTitle>Error</AlertTitle>
                    <AlertDescription>
                        {error || "Goal not found"}
                    </AlertDescription>
                </Alert>
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

            <Card className="mb-8">
                <CardContent className="p-8">
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
                </CardContent>
            </Card>

            {/* Comments Section */}
            <Card>
                <CardHeader>
                    <CardTitle>Comments</CardTitle>
                </CardHeader>
                <CardContent>
                    {/* Add Comment Form */}
                    <form onSubmit={handleSubmitComment} className="mb-8">
                        <div className="mb-4">
                            <Label htmlFor="comment">Add a comment</Label>
                            <Textarea
                                id="comment"
                                value={newComment}
                                onChange={(
                                    e: ChangeEvent<HTMLTextAreaElement>
                                ) => setNewComment(e.target.value)}
                                placeholder="Share your thoughts or progress..."
                                required
                            />
                        </div>
                        <Button
                            type="submit"
                            disabled={submittingComment || !newComment.trim()}
                        >
                            {submittingComment ? "Posting..." : "Post Comment"}
                        </Button>
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
                                                {comment.user?.name ||
                                                    "Anonymous"}
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
                </CardContent>
            </Card>
        </div>
    );
};

export default GoalDetail;
