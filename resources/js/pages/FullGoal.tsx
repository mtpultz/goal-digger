import axios from "axios";
import React, { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import TreeView, { GoalTreeNode } from "../components/TreeView";
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
    children?: Goal[];
    parent?: {
        id: number;
        title: string;
    };
    root?: {
        id: number;
        title: string;
    };
}

const FullGoal: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const [tree, setTree] = useState<GoalTreeNode | null>(null);
    const [loading, setLoading] = useState<boolean>(true);
    const [error, setError] = useState<string>("");

    useEffect(() => {
        if (id) {
            fetchGoalTree();
        }
    }, [id]);

    const fetchGoalTree = async (): Promise<void> => {
        setLoading(true);
        setError("");
        try {
            // Get the selected goal
            const goalResponse = await axios.get(`/api/goals/${id}`);
            const selectedGoal = goalResponse.data.data;

            // Determine which goal to get hierarchy for
            // If it's a root goal, use it directly
            // If it's a child goal, use its root_id to get the full tree
            const hierarchyGoalId = selectedGoal.parent_id
                ? selectedGoal.root_id || selectedGoal.id
                : selectedGoal.id;

            const hierarchyResponse = await axios.get(
                `/api/goals/${hierarchyGoalId}/hierarchy`
            );
            const root = hierarchyResponse.data.data;
            setTree(goalToTreeNode(root));
        } catch (error: any) {
            setError(
                error?.response?.data?.error || "Failed to load goal hierarchy"
            );
        } finally {
            setLoading(false);
        }
    };

    // Convert nested API structure to GoalTreeNode
    const goalToTreeNode = (goal: Goal): GoalTreeNode => {
        return {
            id: goal.id,
            title: goal.title,
            status: goal.status,
            description: goal.description,
            created_at: goal.created_at,
            target_date: goal.target_date,
            children: goal.children
                ? goal.children.map(goalToTreeNode)
                : undefined,
        };
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    if (error || !tree) {
        return (
            <div className="max-w-6xl mx-auto">
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

    const selectedId = id ? parseInt(id) : undefined;

    return (
        <div className="max-w-6xl mx-auto">
            <div className="mb-6">
                <Link
                    to="/goals"
                    className="text-blue-600 hover:text-blue-700 font-medium"
                >
                    ← Back to Goals
                </Link>
            </div>

            <div className="mb-8">
                <h1 className="text-3xl font-bold text-gray-900 mb-2">
                    Goal Hierarchy: {tree.title}
                </h1>
                <p className="text-gray-600">
                    Complete tree view of all goals in this hierarchy
                </p>
            </div>

            <Card>
                <CardContent className="p-6">
                    <TreeView node={tree} selectedId={selectedId} />
                </CardContent>
            </Card>
        </div>
    );
};

export default FullGoal;
