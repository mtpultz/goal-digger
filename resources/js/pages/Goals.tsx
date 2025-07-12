import axios from "axios";
import React, { ChangeEvent, FormEvent, useEffect, useState } from "react";
import { Link } from "react-router-dom";
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
}

interface NewGoal {
    title: string;
    description: string;
    target_date: string;
    category: string;
}

const Goals: React.FC = () => {
    const [goals, setGoals] = useState<Goal[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [error, setError] = useState<string>("");
    const [showCreateForm, setShowCreateForm] = useState<boolean>(false);
    const [newGoal, setNewGoal] = useState<NewGoal>({
        title: "",
        description: "",
        target_date: "",
        category: "personal",
    });

    useEffect(() => {
        fetchGoals();
    }, []);

    const fetchGoals = async (): Promise<void> => {
        try {
            const response = await axios.get("/api/goals?root=true");
            setGoals(response.data.data || []);
        } catch (error) {
            console.error("Error fetching goals:", error);
            setError("Failed to load goals");
        } finally {
            setLoading(false);
        }
    };

    const handleCreateGoal = async (
        e: FormEvent<HTMLFormElement>
    ): Promise<void> => {
        e.preventDefault();
        try {
            const response = await axios.post("/api/goals", newGoal);
            setGoals([...goals, response.data.data]);
            setNewGoal({
                title: "",
                description: "",
                target_date: "",
                category: "personal",
            });
            setShowCreateForm(false);
        } catch (error) {
            console.error("Error creating goal:", error);
            setError("Failed to create goal");
        }
    };

    const handleInputChange = (
        e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
    ): void => {
        setNewGoal({
            ...newGoal,
            [e.target.name]: e.target.value,
        });
    };

    const handleSelectChange = (value: string): void => {
        setNewGoal({
            ...newGoal,
            category: value,
        });
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

    return (
        <div className="max-w-6xl mx-auto">
            <div className="flex justify-between items-center mb-8">
                <h1 className="text-3xl font-bold text-gray-900">My Goals</h1>
                <Button onClick={() => setShowCreateForm(true)}>
                    Create New Goal
                </Button>
            </div>

            {error && (
                <Alert variant="destructive" className="mb-6">
                    <AlertTitle>Error</AlertTitle>
                    <AlertDescription>{error}</AlertDescription>
                </Alert>
            )}

            {showCreateForm && (
                <Card className="mb-8">
                    <CardHeader>
                        <CardTitle>Create New Goal</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleCreateGoal} className="space-y-4">
                            <div>
                                <Label htmlFor="title">Title</Label>
                                <Input
                                    type="text"
                                    name="title"
                                    value={newGoal.title}
                                    onChange={handleInputChange}
                                    required
                                    placeholder="Enter goal title"
                                />
                            </div>
                            <div>
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    name="description"
                                    value={newGoal.description}
                                    onChange={handleInputChange}
                                    placeholder="Enter goal description"
                                />
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="target_date">
                                        Target Date
                                    </Label>
                                    <Input
                                        type="date"
                                        name="target_date"
                                        value={newGoal.target_date}
                                        onChange={handleInputChange}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="category">Category</Label>
                                    <Select
                                        value={newGoal.category}
                                        onValueChange={handleSelectChange}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="personal">
                                                Personal
                                            </SelectItem>
                                            <SelectItem value="professional">
                                                Professional
                                            </SelectItem>
                                            <SelectItem value="health">
                                                Health
                                            </SelectItem>
                                            <SelectItem value="financial">
                                                Financial
                                            </SelectItem>
                                            <SelectItem value="education">
                                                Education
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                            <div className="flex space-x-3">
                                <Button type="submit">Create Goal</Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setShowCreateForm(false)}
                                >
                                    Cancel
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            )}

            {goals.length === 0 ? (
                <Card className="text-center py-12">
                    <CardContent>
                        <div className="text-6xl mb-4">🎯</div>
                        <h3 className="text-xl font-semibold text-gray-900 mb-2">
                            No goals yet
                        </h3>
                        <p className="text-gray-600 mb-6">
                            Start your journey by creating your first goal!
                        </p>
                        <Button onClick={() => setShowCreateForm(true)}>
                            Create Your First Goal
                        </Button>
                    </CardContent>
                </Card>
            ) : (
                <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {goals.map((goal) => (
                        <Card
                            key={goal.id}
                            className="hover:shadow-lg transition-shadow"
                        >
                            <CardContent className="p-6">
                                <div className="flex justify-between items-start mb-4">
                                    <h3 className="text-lg font-semibold text-gray-900">
                                        {goal.title}
                                    </h3>
                                    <span
                                        className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(
                                            goal.status
                                        )}`}
                                    >
                                        {goal.status.replace("_", " ")}
                                    </span>
                                </div>
                                <p className="text-gray-600 mb-4 line-clamp-3">
                                    {goal.description}
                                </p>
                                <div className="space-y-2 text-sm text-gray-500">
                                    <div>
                                        <span className="font-medium">
                                            Category:
                                        </span>{" "}
                                        {goal.category}
                                    </div>
                                    {goal.target_date && (
                                        <div>
                                            <span className="font-medium">
                                                Target:
                                            </span>{" "}
                                            {new Date(
                                                goal.target_date
                                            ).toLocaleDateString()}
                                        </div>
                                    )}
                                    <div>
                                        <span className="font-medium">
                                            Created:
                                        </span>{" "}
                                        {new Date(
                                            goal.created_at
                                        ).toLocaleDateString()}
                                    </div>
                                </div>
                                <div className="mt-4 pt-4 border-t border-gray-200">
                                    <Link
                                        to={`/goals/${goal.id}`}
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
        </div>
    );
};

export default Goals;
