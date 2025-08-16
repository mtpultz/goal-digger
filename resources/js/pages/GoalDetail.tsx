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
import { Input } from "../components/ui/input";
import { Label } from "../components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "../components/ui/select";
import { Textarea } from "../components/ui/textarea";
import EditGoalForm, { EditableGoal } from "../components/EditGoalForm";

interface Goal {
    id: number;
    title: string;
    description: string;
    status: string;
    category: string;
    target_date?: string;
    created_at: string;
    updated_at: string;
    parent_id?: number;
    root_id?: number;
    children?: Goal[];
    parent?: {
        id: number;
        title: string;
    };
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
    const [showSubGoalForm, setShowSubGoalForm] = useState<boolean>(false);
    const [newSubGoal, setNewSubGoal] = useState<{
        title: string;
        description: string;
        target_date: string;
    }>({
        title: "",
        description: "",
        target_date: "",
    });
    const [submittingSubGoal, setSubmittingSubGoal] = useState<boolean>(false);
    const [updatingStatus, setUpdatingStatus] = useState<boolean>(false);
    const [isEditing, setIsEditing] = useState<boolean>(false);

    useEffect(() => {
        if (id) {
            fetchGoal();
            fetchComments();
        }
    }, [id]);

    const fetchGoal = async (): Promise<void> => {
        try {
            const response = await axios.get(
                `/api/goals/${id}?include_children=true`
            );
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

    const handleStatusChange = async (newStatus: string): Promise<void> => {
        if (!goal || newStatus === goal.status) return;

        setUpdatingStatus(true);
        try {
            const response = await axios.patch(`/api/goals/${id}`, {
                status: newStatus,
            });
            setGoal(response.data.data);
        } catch (error) {
            console.error("Error updating status:", error);
            setError("Failed to update status");
        } finally {
            setUpdatingStatus(false);
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

    const handleSubmitSubGoal = async (
        e: FormEvent<HTMLFormElement>
    ): Promise<void> => {
        e.preventDefault();
        if (!newSubGoal.title.trim()) return;

        setSubmittingSubGoal(true);
        try {
            const response = await axios.post("/api/goals", {
                ...newSubGoal,
                parent_id: parseInt(id!),
            });

            // Refresh the goal to get updated children
            await fetchGoal();

            setNewSubGoal({
                title: "",
                description: "",
                target_date: "",
            });
            setShowSubGoalForm(false);
        } catch (error) {
            console.error("Error creating sub-goal:", error);
            setError("Failed to create sub-goal");
        } finally {
            setSubmittingSubGoal(false);
        }
    };

    const handleSubGoalInputChange = (
        e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
    ): void => {
        setNewSubGoal({
            ...newSubGoal,
            [e.target.name]: e.target.value,
        });
    };

    const handleEditSave = async (updatedGoal: EditableGoal): Promise<void> => {
        setIsEditing(false);
        try {
            const response = await axios.patch(`/api/goals/${updatedGoal.id}`, {
                title: updatedGoal.title,
                description: updatedGoal.description,
                category: updatedGoal.category,
                target_date: updatedGoal.target_date,
            });
            setGoal(response.data.data);
        } catch (error) {
            console.error("Error updating goal:", error);
            setError("Failed to update goal");
        }
    };

    const handleEditCancel = (): void => {
        setIsEditing(false);
    };

    const getStatusColor = (status: string): string => {
        switch (status) {
            case "COMPLETE":
                return "bg-green-100 text-green-800";
            case "ACTIVE":
                return "bg-blue-100 text-blue-800";
            case "OPEN":
                return "bg-gray-100 text-gray-800";
            case "SKIPPED":
                return "bg-yellow-100 text-yellow-800";
            default:
                return "bg-gray-100 text-gray-800";
        }
    };

    const getStatusLabel = (status: string): string => {
        switch (status) {
            case "COMPLETE":
                return "Complete";
            case "ACTIVE":
                return "Active";
            case "OPEN":
                return "Open";
            case "SKIPPED":
                return "Skipped";
            default:
                return status;
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

    const subGoals = goal.children || [];

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

            {/* Breadcrumb Navigation */}
            {goal.parent && (
                <div className="mb-4 text-sm text-gray-600">
                    <span>Goal Hierarchy: </span>
                    <Link
                        to={`/goals/${goal.parent.id}`}
                        className="text-blue-600 hover:text-blue-700"
                    >
                        {goal.parent.title}
                    </Link>
                    <span className="mx-2">→</span>
                    <span className="font-medium text-gray-900">
                        {goal.title}
                    </span>
                </div>
            )}

            <Card className="mb-8">
                <CardContent className="p-8">
                    <div className="flex justify-between items-start mb-6">
                        <div className="flex-1">
                            <h1 className="text-3xl font-bold text-gray-900 mb-4">
                                {goal.title}
                            </h1>

                            {/* Status Selector and Edit Button */}
                            <div className="flex items-center space-x-4">
                                <Label
                                    htmlFor="status"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Status:
                                </Label>
                                <Select
                                    value={goal.status}
                                    onValueChange={handleStatusChange}
                                    disabled={updatingStatus}
                                >
                                    <SelectTrigger className="w-48">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="OPEN">
                                            Open
                                        </SelectItem>
                                        <SelectItem value="ACTIVE">
                                            Active
                                        </SelectItem>
                                        <SelectItem value="COMPLETE">
                                            Complete
                                        </SelectItem>
                                        <SelectItem value="SKIPPED">
                                            Skipped
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                {updatingStatus && (
                                    <div className="text-sm text-gray-500">
                                        Updating...
                                    </div>
                                )}
                                {goal.status === "OPEN" && (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => setIsEditing(true)}
                                        className="ml-4"
                                    >
                                        Edit Goal
                                    </Button>
                                )}
                            </div>

                            {/* View Full Tree Button */}
                            <div className="mt-4">
                                <Button asChild variant="outline" size="sm">
                                    <Link to={`/goals/${id}/full`}>
                                        🌳 View Full Tree
                                    </Link>
                                </Button>
                            </div>
                        </div>

                        {/* Current Status Badge */}
                        <span
                            className={`px-3 py-1 rounded-full text-sm font-medium ${getStatusColor(
                                goal.status
                            )}`}
                        >
                            {getStatusLabel(goal.status)}
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

            {/* Sub-Goals Section */}
            <Card className="mb-8">
                <CardHeader>
                    <div className="flex justify-between items-center">
                        <div className="flex items-center space-x-2">
                            <CardTitle>Sub-Goals</CardTitle>
                            {subGoals.length > 0 && (
                                <span className="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">
                                    {subGoals.length}
                                </span>
                            )}
                        </div>
                        <Button
                            onClick={() => setShowSubGoalForm(true)}
                            size="sm"
                        >
                            Add Sub-Goal
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    {showSubGoalForm && (
                        <Card className="mb-6">
                            <CardHeader>
                                <CardTitle>Create New Sub-Goal</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <form
                                    onSubmit={handleSubmitSubGoal}
                                    className="space-y-4"
                                >
                                    <div>
                                        <Label htmlFor="sub-goal-title">
                                            Title
                                        </Label>
                                        <Input
                                            id="sub-goal-title"
                                            name="title"
                                            value={newSubGoal.title}
                                            onChange={handleSubGoalInputChange}
                                            required
                                            placeholder="Enter sub-goal title"
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor="sub-goal-description">
                                            Description
                                        </Label>
                                        <Textarea
                                            id="sub-goal-description"
                                            name="description"
                                            value={newSubGoal.description}
                                            onChange={handleSubGoalInputChange}
                                            placeholder="Enter sub-goal description"
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor="sub-goal-date">
                                            Target Date
                                        </Label>
                                        <Input
                                            id="sub-goal-date"
                                            name="target_date"
                                            type="date"
                                            value={newSubGoal.target_date}
                                            onChange={handleSubGoalInputChange}
                                        />
                                    </div>
                                    <div className="flex space-x-3">
                                        <Button
                                            type="submit"
                                            disabled={submittingSubGoal}
                                        >
                                            {submittingSubGoal
                                                ? "Creating..."
                                                : "Create Sub-Goal"}
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() =>
                                                setShowSubGoalForm(false)
                                            }
                                        >
                                            Cancel
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    )}

                    {subGoals.length === 0 ? (
                        <div className="text-center py-8 text-gray-500">
                            <p>
                                No sub-goals yet. Create your first sub-goal to
                                break down this goal!
                            </p>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {subGoals.map((subGoal) => (
                                <Card
                                    key={subGoal.id}
                                    className="hover:shadow-md transition-shadow"
                                >
                                    <CardContent className="p-4">
                                        <div className="flex justify-between items-start mb-2">
                                            <h4 className="font-semibold text-gray-900">
                                                {subGoal.title}
                                            </h4>
                                            <span
                                                className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(
                                                    subGoal.status
                                                )}`}
                                            >
                                                {getStatusLabel(subGoal.status)}
                                            </span>
                                        </div>
                                        {subGoal.description && (
                                            <p className="text-gray-600 text-sm mb-2">
                                                {subGoal.description}
                                            </p>
                                        )}
                                        <div className="flex justify-between items-center text-xs text-gray-500 mb-3">
                                            <span>
                                                Created:{" "}
                                                {new Date(
                                                    subGoal.created_at
                                                ).toLocaleDateString()}
                                            </span>
                                            {subGoal.target_date && (
                                                <span>
                                                    Target:{" "}
                                                    {new Date(
                                                        subGoal.target_date
                                                    ).toLocaleDateString()}
                                                </span>
                                            )}
                                        </div>
                                        <div className="pt-3 border-t border-gray-200">
                                            <Link
                                                to={`/goals/${subGoal.id}`}
                                                className="text-blue-600 hover:text-blue-700 font-medium text-sm"
                                            >
                                                View Details →
                                            </Link>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* Edit form overlay */}
            {isEditing && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <EditGoalForm
                        goal={{
                            id: goal.id,
                            title: goal.title,
                            description: goal.description,
                            category: goal.category,
                            target_date: goal.target_date,
                        }}
                        onSave={handleEditSave}
                        onCancel={handleEditCancel}
                    />
                </div>
            )}

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
