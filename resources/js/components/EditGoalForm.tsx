import axios from "axios";
import React, { useState } from "react";
import { Button } from "./ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Input } from "./ui/input";
import { Label } from "./ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";
import { Textarea } from "./ui/textarea";
import { Alert, AlertDescription } from "./ui/alert";

export interface EditableGoal {
    id: number;
    title: string;
    description: string;
    category: string;
    target_date?: string;
}

interface EditGoalFormProps {
    goal: EditableGoal;
    onSave: (updatedGoal: any) => void;
    onCancel: () => void;
}

const EditGoalForm: React.FC<EditGoalFormProps> = ({ goal, onSave, onCancel }) => {
    const [formData, setFormData] = useState<EditableGoal>({
        id: goal.id,
        title: goal.title,
        description: goal.description,
        category: goal.category || "personal",
        target_date: goal.target_date || "",
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value,
        });
    };

    const handleSelectChange = (value: string) => {
        setFormData({
            ...formData,
            category: value,
        });
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError("");

        try {
            const response = await axios.patch(`/api/goals/${goal.id}`, {
                title: formData.title,
                description: formData.description,
                category: formData.category,
                target_date: formData.target_date || null,
            });
            
            onSave(response.data.data);
        } catch (err: any) {
            setError(err?.response?.data?.message || "Failed to update goal");
        } finally {
            setLoading(false);
        }
    };

    return (
        <Card className="w-full max-w-md">
            <CardHeader>
                <CardTitle>Edit Goal</CardTitle>
            </CardHeader>
            <CardContent>
                {error && (
                    <Alert variant="destructive" className="mb-4">
                        <AlertDescription>{error}</AlertDescription>
                    </Alert>
                )}
                
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <Label htmlFor="title">Title</Label>
                        <Input
                            type="text"
                            name="title"
                            value={formData.title}
                            onChange={handleInputChange}
                            required
                            placeholder="Enter goal title"
                        />
                    </div>
                    
                    <div>
                        <Label htmlFor="description">Description</Label>
                        <Textarea
                            name="description"
                            value={formData.description}
                            onChange={handleInputChange}
                            placeholder="Enter goal description"
                            rows={3}
                        />
                    </div>
                    
                    <div>
                        <Label htmlFor="target_date">Target Date</Label>
                        <Input
                            type="date"
                            name="target_date"
                            value={formData.target_date}
                            onChange={handleInputChange}
                        />
                    </div>
                    
                    <div>
                        <Label htmlFor="category">Category</Label>
                        <Select value={formData.category} onValueChange={handleSelectChange}>
                            <SelectTrigger>
                                <SelectValue placeholder="Select category" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="personal">Personal</SelectItem>
                                <SelectItem value="professional">Professional</SelectItem>
                                <SelectItem value="health">Health</SelectItem>
                                <SelectItem value="financial">Financial</SelectItem>
                                <SelectItem value="education">Education</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    
                    <div className="flex space-x-3 pt-4">
                        <Button type="submit" disabled={loading}>
                            {loading ? "Saving..." : "Save Changes"}
                        </Button>
                        <Button type="button" variant="outline" onClick={onCancel}>
                            Cancel
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
};

export default EditGoalForm;