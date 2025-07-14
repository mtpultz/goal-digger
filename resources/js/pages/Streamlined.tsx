import axios from "axios";
import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { Card, CardContent } from "../components/ui/card";

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
    root?: {
        id: number;
        title: string;
    };
    parent?: {
        id: number;
        title: string;
    };
    comments_count?: number;
}

const Streamlined: React.FC = () => {
    const [activeGoals, setActiveGoals] = useState<Goal[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [error, setError] = useState<string>("");

    useEffect(() => {
        fetchActiveGoals();
    }, []);

    const fetchActiveGoals = async (): Promise<void> => {
        try {
            const response = await axios.get("/api/goals/active");
            setActiveGoals(response.data.data || []);
        } catch (error) {
            console.error("Error fetching active goals:", error);
            setError("Failed to load active goals");
        } finally {
            setLoading(false);
        }
    };

    const getRootGoalTitle = (goal: Goal): string => {
        if (goal.root) {
            return goal.root.title;
        }
        if (goal.parent) {
            return goal.parent.title;
        }
        return goal.title; // If it's a root goal itself
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="max-w-4xl mx-auto p-6">
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {error}
                </div>
            </div>
        );
    }

    return (
        <div className="max-w-4xl mx-auto p-6">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-gray-900 mb-2">
                    Active Goals
                </h1>
                <p className="text-gray-600">
                    Focus on what matters most right now
                </p>
            </div>

            {activeGoals.length === 0 ? (
                <Card className="text-center py-12">
                    <CardContent>
                        <div className="text-6xl mb-4">🎯</div>
                        <h3 className="text-xl font-semibold text-gray-900 mb-2">
                            No active goals
                        </h3>
                        <p className="text-gray-600 mb-6">
                            All your goals are either completed or not yet
                            started.
                        </p>
                        <Link
                            to="/goals"
                            className="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium"
                        >
                            View All Goals
                        </Link>
                    </CardContent>
                </Card>
            ) : (
                <div className="space-y-4">
                    {activeGoals.map((goal) => (
                        <Card
                            key={goal.id}
                            className="hover:shadow-lg transition-shadow"
                        >
                            <CardContent className="p-6">
                                <div className="flex justify-between items-start">
                                    <div className="flex-1">
                                        {/* Root Goal Title (small) */}
                                        <div className="text-sm text-gray-500 mb-1">
                                            {getRootGoalTitle(goal)}
                                        </div>

                                        {/* Active Goal Title (large) */}
                                        <Link
                                            to={`/goals/${goal.id}`}
                                            className="block"
                                        >
                                            <h2 className="text-xl font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                                                {goal.title}
                                            </h2>
                                        </Link>

                                        {/* Goal Description */}
                                        {goal.description && (
                                            <p className="text-gray-600 mt-2 line-clamp-2">
                                                {goal.description}
                                            </p>
                                        )}
                                    </div>

                                    {/* Comments Count */}
                                    <div className="ml-4 flex items-center space-x-2">
                                        <span className="text-sm text-gray-500">
                                            💬 {goal.comments_count || 0}
                                        </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            )}
        </div>
    );
};

export default Streamlined;
