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
    const [rootGoal, setRootGoal] = useState<Goal | null>(null);
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
            setRootGoal(selectedGoal);

            // If it's a root goal, use the new endpoint
            if (!selectedGoal.parent_id) {
                const hierarchyResponse = await axios.get(
                    `/api/goals/${id}/hierarchy`
                );
                const root = hierarchyResponse.data.data;
                setTree(goalToTreeNode(root));
            } else {
                // Fallback: build tree from flat list as before
                const rootGoalId = selectedGoal.root_id || selectedGoal.id;
                const allGoalsResponse = await axios.get("/api/goals");
                const allGoalsData = allGoalsResponse.data.data || [];
                const hierarchyGoals = allGoalsData.filter(
                    (goal: Goal) =>
                        goal.root_id === rootGoalId || goal.id === rootGoalId
                );
                const root = hierarchyGoals.find(
                    (g: Goal) => g.id === rootGoalId
                );
                setTree(root ? buildTree(root, hierarchyGoals) : null);
            }
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

    // Convert flat list to tree (fallback)
    const buildTree = (root: Goal, goals: Goal[]): GoalTreeNode => {
        const build = (goal: Goal): GoalTreeNode => {
            const children = goals
                .filter((g) => g.parent_id === goal.id)
                .map(build);
            return {
                id: goal.id,
                title: goal.title,
                status: goal.status,
                description: goal.description,
                created_at: goal.created_at,
                target_date: goal.target_date,
                children: children.length > 0 ? children : undefined,
            };
        };
        return build(root);
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
